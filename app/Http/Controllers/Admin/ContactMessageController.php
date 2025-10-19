<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ContactMessagesDataTable;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(ContactMessagesDataTable $dataTable): View|JsonResponse
    {
        return $dataTable->render('admin.contact.index');
    }

    public function show(ContactMessage $message): View
    {
        $message->load(['replies' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'handler:id,name']);

        return view('admin.contact.show', compact('message'));
    }

    public function update(Request $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved,spam'],
            'notes' => ['nullable', 'string'],
        ]);

        $wasNew = $message->status === 'new';
        $newStatus = $validated['status'];

        $message->status = $newStatus;
        $message->notes = $validated['notes'] ?? $message->notes;

        if ($wasNew && $newStatus !== 'new') {
            $message->handled_by = auth()->id();
            $message->handled_at = now();
        }

        $message->save();

        return redirect()
            ->back()
            ->with('success', 'Pesan berhasil diperbarui.');
    }
}
