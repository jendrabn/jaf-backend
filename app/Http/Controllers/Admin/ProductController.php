<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Traits\MediaUploadingTrait;
use App\Traits\QuillUploadImage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    use MediaUploadingTrait;
    use QuillUploadImage;

    /**
     * Display a listing of the products.
     */
    public function index(ProductsDataTable $dataTable): mixed
    {
        $product_categories = ProductCategory::pluck('name', 'id')->prepend('All', null);
        $product_brands = ProductBrand::pluck('name', 'id')->prepend('All', null);

        return $dataTable->render('admin.products.index', compact('product_categories', 'product_brands'));
    }

    /**
     * Display a specific product.
     *
     * @param  Product  $product  The product to display.
     */
    public function show(Product $product): View
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Display a form to create a new product.
     */
    public function create(): View
    {
        $product_categories = ProductCategory::pluck('name', 'id')->prepend('---', null);
        $product_brands = ProductBrand::pluck('name', 'id')->prepend('---', null);

        return view('admin.products.create', compact('product_brands', 'product_categories'));
    }

    /**
     * Handles the storing of a new product.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        foreach ($request->input('images', []) as $file) {
            $product->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $product->id]);
        }

        toastr('Product created successfully.', 'success');

        return redirect()->route('admin.products.index');
    }

    public function edit(Product $product)
    {
        $product_categories = ProductCategory::pluck('name', 'id')->prepend('---', null);
        $product_brands = ProductBrand::pluck('name', 'id')->prepend('---', null);

        $product->load('category', 'brand');

        return view('admin.products.edit', compact('product', 'product_brands', 'product_categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        if (count($product->images) > 0) {
            foreach ($product->images as $media) {
                if (! in_array($media->file_name, $request->input('images', []))) {
                    $media->delete();
                }
            }
        }
        $media = $product->images->pluck('file_name')->toArray();
        foreach ($request->input('images', []) as $file) {
            if (count($media) === 0 || ! in_array($file, $media)) {
                $product->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
            }
        }

        toastr('Product updated successfully.', 'success');

        return back();
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function massDestroy(ProductRequest $request)
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Product::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_products',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' products.'],
            ],
            subjectId: null,
            subjectType: \App\Models\Product::class
        );

        return response()->json(['message' => 'Products deleted successfully.']);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        return $this->quillUploadImage($request);
    }

    public function storeCKEditorImages(Request $request)
    {
        $model = new Product;
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json([
            'filename' => $media->file_name,
            'uploaded' => 1,
            'url' => $media->getUrl(),
        ], Response::HTTP_CREATED);
    }
}
