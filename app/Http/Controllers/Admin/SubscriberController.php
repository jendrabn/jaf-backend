<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SubscribersDataTable;
use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SubscribersDataTable $dataTable): View|JsonResponse
    {
        return $dataTable->render('admin.subscribers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.subscribers.partials.modal');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriberRequest $request): JsonResponse
    {
        $subscriber = Subscriber::create([
            'email' => $request->email,
            'name' => $request->name,
            'token' => \Illuminate\Support\Str::random(64),
            'status' => SubscriberStatus::from($request->status),
            'subscribed_at' => $request->status === SubscriberStatus::Subscribed->value ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscriber created successfully',
            'data' => $subscriber,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscriber $subscriber): View
    {
        return view('admin.subscribers.partials.modal', compact('subscriber'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriberRequest $request, Subscriber $subscriber): JsonResponse
    {
        $subscriber->update([
            'email' => $request->email,
            'name' => $request->name,
            'status' => SubscriberStatus::from($request->status),
            'subscribed_at' => $request->status === SubscriberStatus::Subscribed->value && ! $subscriber->subscribed_at ? now() : $subscriber->subscribed_at,
            'unsubscribed_at' => $request->status === SubscriberStatus::Unsubscribed->value && ! $subscriber->unsubscribed_at ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscriber updated successfully',
            'data' => $subscriber,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscriber $subscriber): JsonResponse
    {
        $subscriber->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscriber deleted successfully',
        ]);
    }

    /**
     * Remove multiple resources from storage.
     */
    public function massDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:subscribers,id',
        ]);

        Subscriber::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected subscribers deleted successfully',
        ]);
    }
}
