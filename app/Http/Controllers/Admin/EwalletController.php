<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\EwalletsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EwalletRequest;
use App\Models\Ewallet;
use App\Traits\MediaUploadingTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class EwalletController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(EwalletsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.ewallets.index');
    }

    /**
     * Display the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.ewallets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EwalletRequest $request): RedirectResponse
    {
        $ewallet = Ewallet::create($request->validated());

        if ($request->input('logo', false)) {
            $path = storage_path('tmp/uploads/'.basename($request->input('logo')));
            $ewallet->addMedia($path)->toMediaCollection(Ewallet::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $ewallet->id]);
        }

        toastr('Ewallet created successfully.', 'success');

        return to_route('admin.ewallets.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ewallet $ewallet): View
    {
        return view('admin.ewallets.edit', compact('ewallet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EwalletRequest $request, Ewallet $ewallet): RedirectResponse
    {
        $ewallet->update($request->validated());

        if ($request->input('logo', false)) {
            if (! $ewallet->logo || $request->input('logo') !== $ewallet->logo->file_name) {
                if ($ewallet->logo) {
                    $ewallet->logo->delete();
                }
                $path = storage_path('tmp/uploads/'.basename($request->input('logo')));
                $ewallet->addMedia($path)->toMediaCollection(Ewallet::MEDIA_COLLECTION_NAME);
            }
        } elseif ($ewallet->logo) {
            $ewallet->logo->delete();
        }

        toastr('Ewallet updated successfully.', 'success');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ewallet $ewallet): JsonResponse
    {
        $ewallet->delete();

        return response()->json(['message' => 'Ewallet deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource(s) from storage.
     */
    public function massDestroy(EwalletRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Ewallet::whereIn('id', $request->validated('ids'))->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_ewallets',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' ewallets.'],
            ],
            subjectId: null,
            subjectType: \App\Models\Ewallet::class
        );

        return response()->json(['message' => 'Ewallet deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Store a newly uploaded media in storage using CKEditor.
     */
    public function storeCKEditorImages(Request $request): JsonResponse
    {
        $model = new Ewallet;
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
