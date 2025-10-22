<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CampaignsDataTable;
use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignRequest;
use App\Jobs\DispatchCampaignJob;
use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Campaign Newsletter controller.
 *
 * Features:
 * - CRUD campaigns
 * - Send campaign to all subscribers (queued)
 * - Test send campaign to a single email (queued)
 * - View campaign detail & statistics
 */
class CampaignController extends Controller
{
    /**
     * Display a listing of the campaigns using Yajra DataTable.
     */
    public function index(CampaignsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.campaigns.index');
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create(): View
    {
        return view('admin.campaigns.create');
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(CampaignRequest $request): RedirectResponse
    {
        $campaign = Campaign::create($request->validated());

        toastr('Campaign created successfully', 'success');

        return redirect()->route('admin.campaigns.edit', $campaign);
    }

    /**
     * Display the specified campaign details and statistics.
     */
    public function show(Campaign $campaign): View
    {
        $stats = CampaignReceipt::query()
            ->selectRaw('status, COUNT(*) as count')
            ->where('campaign_id', $campaign->id)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.campaigns.show', compact('campaign', 'stats'));
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(CampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $campaign->update($request->validated());

        toastr('Campaign updated successfully', 'success');

        return back();
    }

    /**
     * Remove the specified campaign from storage.
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Queue sending this campaign to all subscribed subscribers.
     */
    public function sendAll(Campaign $campaign): JsonResponse
    {
        // Update campaign status to SENDING
        $campaign->status = CampaignStatus::SENDING;
        $campaign->scheduled_at = $campaign->scheduled_at ?? now();
        $campaign->save();

        // Dispatch dispatcher job to enqueue per-recipient jobs
        DispatchCampaignJob::dispatch($campaign->id);

        return response()->json(['message' => 'Campaign is being sent to all subscribers.'], Response::HTTP_OK);
    }

    /**
     * Queue a test send to a single email address.
     */
    public function testSend(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure subscriber exists (or create a temporary subscribed one)
        $subscriber = Subscriber::query()->firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'] ?? null,
                'token' => Str::random(64),
                'status' => SubscriberStatus::Subscribed->value,
                'subscribed_at' => now(),
            ]
        );

        // Create or ensure a receipt exists for this campaign & subscriber
        CampaignReceipt::query()->firstOrCreate(
            [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
            ],
            [
                'status' => 'queued',
            ]
        );

        // Dispatch a single send job
        SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);

        return response()->json(['message' => 'Test email queued successfully.'], Response::HTTP_OK);
    }

    /**
     * Upload image for Quill editor, compress to WebP <= 250KB, and max width 1200px (height auto).
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // accept up to ~5MB raw upload
        ]);

        $file = $request->file('image');

        // Prepare Intervention Image manager
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        // Resize down to max width 1200px, height auto while preserving aspect ratio
        $maxWidth = 1200;
        $currentWidth = null;

        try {
            $currentWidth = method_exists($image, 'width') ? $image->width() : null;
        } catch (\Throwable $e) {
            $currentWidth = null;
        }

        if ($currentWidth && $currentWidth > $maxWidth) {
            if (method_exists($image, 'scale')) {
                $image = $image->scale($maxWidth);
            } elseif (method_exists($image, 'resize')) {
                $ratio = $maxWidth / $currentWidth;
                $newHeight = (int) round($ratio * ($image->height() ?? 0));
                $image = $image->resize($maxWidth, $newHeight);
            }
            $currentWidth = $maxWidth;
        }

        // Ensure public/campaigns directory exists
        Storage::disk('public')->makeDirectory('campaigns');

        // Unique filename
        $filename = Str::uuid()->toString() . '.webp';
        $destPath = storage_path('app/public/campaigns/' . $filename);

        // Target <= 250KB by lowering quality; if still large, reduce width progressively (down to 600px)
        $maxBytes = 250 * 1024;
        $quality = 80;
        $minQuality = 30;

        $saveAndSize = function () use ($image, &$quality, $destPath): int {
            $image->toWebp($quality)->save($destPath);
            clearstatcache(true, $destPath);
            return (int) (is_file($destPath) ? filesize($destPath) : PHP_INT_MAX);
        };

        $size = $saveAndSize();
        $resizeAttempts = 0;

        while ($size > $maxBytes && $resizeAttempts < 5) {
            // Reduce quality first
            while ($size > $maxBytes && $quality > $minQuality) {
                $quality -= 10;
                $size = $saveAndSize();
            }

            // If still too large, reduce width by 10% (not below 600px), then retry from default quality
            if ($size > $maxBytes) {
                $resizeAttempts++;
                $quality = 80;

                if ($currentWidth) {
                    $newWidth = max(600, (int) round($currentWidth * 0.9));
                    if ($newWidth < $currentWidth) {
                        if (method_exists($image, 'scale')) {
                            $image = $image->scale($newWidth);
                        } elseif (method_exists($image, 'resize')) {
                            $ratio = $newWidth / $currentWidth;
                            $newHeight = (int) round($ratio * ($image->height() ?? 0));
                            $image = $image->resize($newWidth, $newHeight);
                        }
                        $currentWidth = $newWidth;
                    }
                }

                $size = $saveAndSize();
            }
        }

        $url = asset('storage/campaigns/' . $filename);

        return response()->json([
            'url' => $url,
            'bytes' => $size,
            'quality' => $quality,
            'width' => $currentWidth,
        ], Response::HTTP_OK);
    }
}
