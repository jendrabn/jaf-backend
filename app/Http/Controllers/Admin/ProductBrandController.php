<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Models\ProductBrand;
use Illuminate\Http\JsonResponse;
use App\Traits\MediaUploadingTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\DataTables\ProductBrandsDataTable;
use App\Http\Requests\Admin\ProductBrandRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductBrandController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Renders the index view for the ProductBrandsDataTable.
     *
     * @param ProductBrandsDataTable $dataTable
     * @return mixed
     */
    public function index(ProductBrandsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.productBrands.index');
    }

    /**
     * Display the create form for a new product brand.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.productBrands.create');
    }

    /**
     * Store a new product brand.
     *
     * @param ProductBrandRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductBrandRequest $request)
    {
        $productBrand = ProductBrand::create($request->validated());

        if ($request->input('logo', false)) {
            $productBrand->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))
                ->toMediaCollection(ProductBrand::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $productBrand->id]);
        }

        toastr('Product brand created successfully.', 'success');

        return to_route('admin.product-brands.index');
    }

    /**
     * Displays the edit view for a product brand.
     *
     * @param ProductBrand $productBrand
     * @return \Illuminate\View\View
     */
    public function edit(ProductBrand $productBrand): View
    {
        $productBrand->loadCount('products');

        return view('admin.productBrands.edit', compact('productBrand'));
    }

    /**
     * Updates a product brand with the given request data.
     *
     * @param ProductBrandRequest $request
     * @param ProductBrand $productBrand
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductBrandRequest $request, ProductBrand $productBrand): RedirectResponse
    {
        $productBrand->update($request->validated());

        if ($request->input('logo', false)) {
            if (!$productBrand->logo || $request->input('logo') !== $productBrand->logo->file_name) {
                if ($productBrand->logo) {
                    $productBrand->logo->delete();
                }

                $productBrand->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))
                    ->toMediaCollection(ProductBrand::MEDIA_COLLECTION_NAME);
            }
        } elseif ($productBrand->logo) {
            $productBrand->logo->delete();
        }

        toastr('Product brand updated successfully.', 'success');

        return back();
    }

    /**
     * Deletes a product brand from the database.
     *
     * @param ProductBrand $productBrand
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProductBrand $productBrand): JsonResponse
    {
        $productBrand->delete();

        return response()->json(['message' => 'Product brand deleted successfully.']);
    }

    /**
     * Deletes multiple product brands based on the provided request.
     *
     * @param ProductBrandRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(ProductBrandRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        ProductBrand::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_product_brands',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' product brands.'],
            ],
            subjectId: null,
            subjectType: \App\Models\ProductBrand::class
        );

        return response()->json(['message' => 'Product brands deleted successfully.']);
    }
}
