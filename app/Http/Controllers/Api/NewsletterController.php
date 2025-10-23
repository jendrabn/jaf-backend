<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscribeRequest;
use App\Http\Resources\SubscriberResource;
use App\Jobs\SendSubscribeNotificationJob;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter with rate limiting
     */
    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $key = 'subscribe:'.$request->ip();
        $response = null;

        $executed = RateLimiter::attempt(
            $key,
            1, // Max attempts
            function () use ($request, &$response) {
                $subscriber = Subscriber::create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'token' => Str::random(64),
                    'status' => SubscriberStatus::Subscribed,
                    'subscribed_at' => now(),
                ]);

                // Send notification email via queue
                SendSubscribeNotificationJob::dispatch($subscriber);

                $response = response()->json([
                    'success' => true,
                    'message' => 'Thank you for subscribing to our newsletter!',
                    'data' => new SubscriberResource($subscriber),
                ], 201);
            },
            300 // 5 minutes in seconds
        );

        if (! $executed) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => 'Too many subscription attempts. Please try again in '.($seconds > 60 ? $minutes.' minutes' : $seconds.' seconds.'),
            ], 429);
        }

        /** @var JsonResponse $response */
        return $response ?? response()->json([
            'success' => false,
            'message' => 'Unable to process subscription',
        ], 500);
    }

    /**
     * Unsubscribe from newsletter using token
     */
    public function unsubscribe(string $token): JsonResponse
    {
        $subscriber = Subscriber::where('token', $token)->first();

        if (! $subscriber) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid unsubscribe token',
            ], 404);
        }

        if ($subscriber->status === SubscriberStatus::Unsubscribed) {
            return response()->json([
                'success' => true,
                'message' => 'You are already unsubscribed from our newsletter',
            ]);
        }

        $subscriber->update([
            'status' => SubscriberStatus::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have been successfully unsubscribed from our newsletter',
        ]);
    }

    /**
     * Confirm newsletter subscription
     */
    public function confirm(string $token): JsonResponse
    {
        $subscriber = Subscriber::where('token', $token)->first();

        if (! $subscriber) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid confirmation token',
            ], 404);
        }

        if ($subscriber->status === SubscriberStatus::Subscribed) {
            return response()->json([
                'success' => true,
                'message' => 'Your subscription is already confirmed',
            ]);
        }

        $subscriber->update([
            'status' => SubscriberStatus::Subscribed,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your newsletter subscription has been confirmed',
        ]);
    }

    /**
     * Track email open
     */
    public function trackOpen(int $receipt, string $token): BinaryFileResponse
    {
        $campaignReceipt = CampaignReceipt::find($receipt);

        if (! $campaignReceipt || $campaignReceipt->subscriber->token !== $token) {
            return response()->file(public_path('images/1x1.png'));
        }

        // Only update if not already opened
        if ($campaignReceipt->status !== 'opened' && $campaignReceipt->status !== 'clicked') {
            $campaignReceipt->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }

        // Return 1x1 transparent pixel
        return response()->file(public_path('images/1x1.png'));
    }

    /**
     * Track email click
     */
    public function trackClick(int $receipt, string $token): JsonResponse
    {
        $campaignReceipt = CampaignReceipt::find($receipt);

        if (! $campaignReceipt || $campaignReceipt->subscriber->token !== $token) {
            return response()->json(['error' => 'Invalid tracking data'], 404);
        }

        // Update status to clicked
        $campaignReceipt->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);

        // Get the original URL from query parameter
        $url = request('url');

        if ($url) {
            return response()->json([
                'success' => true,
                'redirect_url' => urldecode($url),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
        ]);
    }

    /**
     * Show email web view
     */
    public function webview(int $receipt, string $token): JsonResponse
    {
        $campaignReceipt = CampaignReceipt::find($receipt);

        if (! $campaignReceipt || $campaignReceipt->subscriber->token !== $token) {
            return response()->json(['error' => 'Invalid webview data'], 404);
        }

        // Update status to opened if not already
        if ($campaignReceipt->status !== 'opened' && $campaignReceipt->status !== 'clicked') {
            $campaignReceipt->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }

        $campaign = $campaignReceipt->campaign;
        $subscriber = $campaignReceipt->subscriber;

        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => [
                    'name' => $campaign->name,
                    'subject' => $campaign->subject,
                    'content' => $campaign->content,
                    'created_at' => $campaign->created_at->format('d M Y H:i'),
                ],
                'subscriber' => [
                    'email' => $subscriber->email,
                    'name' => $subscriber->name,
                ],
            ],
        ]);
    }
}
