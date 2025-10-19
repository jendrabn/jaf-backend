<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactAutoReplyMail;
use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Sanitize (mirror StoreContactMessageRequest::prepareForValidation)
        $payload = [
            'name' => trim(strip_tags((string) $request->input('name', ''))),
            'email' => trim((string) $request->input('email', '')),
            'phone' => ($request->filled('phone') ? trim(strip_tags((string) $request->input('phone'))) : null),
            'message' => trim(strip_tags((string) $request->input('message', ''))),
        ];

        // Validate first
        $validator = Validator::make($payload, (new StoreContactMessageRequest)->rules());
        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $validator->validated();

        // Rate limit AFTER validation
        $key = $this->rateLimitKey($request, $data['email'] ?? null);
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retry = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Terlalu sering mengirim pesan. Coba lagi setelah 5 menit.',
                'retry_after' => $retry,
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes

        // Persist
        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
            'ip' => $request->ip(),
            'user_agent' => (string) $request->header('User-Agent'),
        ]);

        // Queue emails (support + auto-reply)
        $supportEmail = config('mail.support_email') ?? env('SUPPORT_EMAIL');
        if (! empty($supportEmail)) {
            Mail::to($supportEmail)->queue(new NewContactMessageMail($message));
        }
        if (! empty($message->email)) {
            Mail::to($message->email)->queue(new ContactAutoReplyMail($message));
        }

        return response()->json([
            'id' => $message->id,
            'status' => 'received',
            'message' => 'Terima kasih, pesan Anda sudah kami terima.',
        ], 201);
    }

    public function storeWeb(Request $request): RedirectResponse
    {
        // Sanitize
        $payload = [
            'name' => trim(strip_tags((string) $request->input('name', ''))),
            'email' => trim((string) $request->input('email', '')),
            'phone' => ($request->filled('phone') ? trim(strip_tags((string) $request->input('phone'))) : null),
            'message' => trim(strip_tags((string) $request->input('message', ''))),
        ];

        // Validate first
        $validator = Validator::make($payload, (new StoreContactMessageRequest)->rules());
        $validator->validate();
        $data = $validator->validated();

        // Rate limit AFTER validation
        $key = $this->rateLimitKey($request, $data['email'] ?? null);
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retry = RateLimiter::availableIn($key);

            return redirect()->back()
                ->withErrors(['message' => 'Terlalu sering mengirim pesan. Coba lagi setelah 5 menit.'])
                ->with('retry_after', $retry);
        }
        RateLimiter::hit($key, 300); // 5 minutes

        // Persist
        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
            'ip' => $request->ip(),
            'user_agent' => (string) $request->header('User-Agent'),
        ]);

        // Queue emails (support + auto-reply)
        $supportEmail = config('mail.support_email') ?? env('SUPPORT_EMAIL');
        if (! empty($supportEmail)) {
            Mail::to($supportEmail)->queue(new NewContactMessageMail($message));
        }
        if (! empty($message->email)) {
            Mail::to($message->email)->queue(new ContactAutoReplyMail($message));
        }

        return redirect()->back()->with('success', 'Terima kasih, pesan Anda sudah kami terima.');
    }

    private function rateLimitKey(Request $request, ?string $email = null): string
    {
        $emailRaw = $email !== null ? trim((string) $email) : trim((string) $request->input('email', ''));
        $emailValid = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? strtolower($emailRaw) : '';

        return 'contact:ip:'.$request->ip().':email:'.$emailValid;
    }
}
