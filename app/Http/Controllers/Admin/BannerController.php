<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\MediaUploadingTrait;
use App\DataTables\BannersDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\BannerRequest;
use Symfony\Component\HttpFoundation\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BannerController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Display a listing of the resource.
     *
     * @param BannersDataTable $dataTable
     * @return Response
     */
    public function index(BannersDataTable $dataTable)
    {
        return $dataTable->render("admin.banners.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BannerRequest $request
     * @return RedirectResponse
     */
    public function store(BannerRequest $request): RedirectResponse
    {
        $banner = Banner::create($request->validated());

        if ($request->input('image', false)) {
            $banner->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))
                ->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $banner->id]);
        }

        return redirect()->route('admin.banners.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Banner $banner
     * @return View
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BannerRequest $request
     * @param Banner $banner
     * @return RedirectResponse
     */
    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $banner->update($request->validated());

        if ($request->input('image', false)) {
            if (!$banner->image || $request->input('image') !== $banner->image->file_name) {
                if ($banner->image) {
                    $banner->image->delete();
                }

                $path = storage_path('tmp/uploads/' . basename($request->input('image')));
                $banner->addMedia($path)->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);
            }
        } elseif ($banner->image) {
            $banner->image->delete();
        }

        return redirect()->route('admin.banners.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Banner $banner
     * @return JsonResponse
     */
    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BannerRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BannerRequest $request)
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Banner::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_banners',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' banners']
            ],
            subjectId: null,
            subjectType: \App\Models\Banner::class
        );

        return response()->json(['message' => 'Banners deleted successfully.'], Response::HTTP_OK);
    }

    public function storeCKEditorImages(Request $request)
    {
        $model = new Banner();
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
