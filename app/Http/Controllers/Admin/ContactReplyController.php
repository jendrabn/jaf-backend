<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReplyRequest;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use App\Models\ContactReply;
use App\Traits\QuillUploadImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactReplyController extends Controller
{
    use QuillUploadImage;

    public function store(StoreReplyRequest $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validated();

        $reply = ContactReply::query()->create([
            'contact_message_id' => $message->id,
            'admin_id' => auth()->id(),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => 'draft',
        ]);

        try {
            // Queue email to the message sender
            Mail::to($message->email)->queue(new ContactReplyMail($reply, $message));

            // Mark as sent
            $reply->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Balasan berhasil dikirim.');
        } catch (\Throwable $e) {
            // Mark as failed
            $reply->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengirim balasan: '.$e->getMessage());
        }
    }

    public function uploadImage(Request $request): JsonResponse
    {
        return $this->quillUploadImage($request);
    }
}
