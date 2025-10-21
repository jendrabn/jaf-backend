<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsletterRequest;
use App\Http\Resources\NewsletterResource;
use App\Models\Newsletter;
use Illuminate\Http\JsonResponse;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(StoreNewsletterRequest $request): JsonResponse
    {
        $newsletter = Newsletter::create([
            'email' => $request->email,
            'name' => $request->name,
            'status' => Newsletter::STATUS_PENDING,
        ]);

        // TODO: Send confirmation email with unsubscribe token

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih telah berlangganan newsletter. Silakan cek email untuk konfirmasi.',
            'data' => new NewsletterResource($newsletter),
        ], 201);
    }

    /**
     * Unsubscribe from newsletter using token
     */
    public function unsubscribe(string $token): JsonResponse
    {
        $newsletter = Newsletter::where('unsubscribe_token', $token)->first();

        if (! $newsletter) {
            return response()->json([
                'success' => false,
                'message' => 'Token unsubscribe tidak valid',
            ], 404);
        }

        if ($newsletter->isUnsubscribed()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda sudah unsubscribe dari newsletter',
            ]);
        }

        $newsletter->unsubscribe();

        return response()->json([
            'success' => true,
            'message' => 'Anda berhasil unsubscribe dari newsletter',
        ]);
    }

    /**
     * Confirm newsletter subscription
     */
    public function confirm(string $token): JsonResponse
    {
        $newsletter = Newsletter::where('unsubscribe_token', $token)->first();

        if (! $newsletter) {
            return response()->json([
                'success' => false,
                'message' => 'Token konfirmasi tidak valid',
            ], 404);
        }

        if ($newsletter->isConfirmed()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription anda sudah dikonfirmasi',
            ]);
        }

        $newsletter->confirm();

        return response()->json([
            'success' => true,
            'message' => 'Subscription newsletter berhasil dikonfirmasi',
        ]);
    }
}
