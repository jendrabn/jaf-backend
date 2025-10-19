<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BanksDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankRequest;
use App\Models\Bank;
use App\Traits\MediaUploadingTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BankController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Displays a listing of the resource.
     */
    public function index(BanksDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.banks.index');
    }

    /**
     * Display a form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.banks.create');
    }

    public function store(BankRequest $request): RedirectResponse
    {
        $bank = Bank::create($request->validated());

        if ($request->input('logo', false)) {
            $path = storage_path('tmp/uploads/'.basename($request->input('logo')));
            $bank->addMedia($path)->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $bank->id]);
        }

        toastr('Bank created successfully.', 'success');

        return to_route('admin.banks.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank): View
    {
        return view('admin.banks.edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank): RedirectResponse
    {
        $bank->update($request->validated());

        if ($request->input('logo', false)) {
            if (! $bank->logo || $request->input('logo') !== $bank->logo->file_name) {
                if ($bank->logo) {
                    $bank->logo->delete();
                }
                $path = storage_path('tmp/uploads/'.basename($request->input('logo')));
                $bank->addMedia($path)->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);
            }
        } elseif ($bank->logo) {
            $bank->logo->delete();
        }

        toastr('Bank updated successfully.', 'success');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank): JsonResponse
    {
        $bank->delete();

        return response()->json(['message' => 'Bank deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Destroy the specified resource(s) from storage.
     */
    public function massDestroy(BankRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Bank::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_banks',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' banks'],
            ],
            subjectId: null,
            subjectType: \App\Models\Bank::class
        );

        return response()->json(['message' => 'Bank deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Store a newly uploaded media in storage using CKEditor.
     */
    public function storeCKEditorImages(Request $request): JsonResponse
    {
        $model = new Bank;
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
