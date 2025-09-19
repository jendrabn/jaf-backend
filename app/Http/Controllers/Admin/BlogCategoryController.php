<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogCategoriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use App\Traits\MediaUploadingTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BlogCategoryController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Display a listing of the resource.
     *
     * @param BlogCategoriesDataTable $dataTable
     * @return mixed
     */
    public function index(BlogCategoriesDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogCategories.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogCategoryRequest $request
     * @return JsonResponse
     */
    public function store(BlogCategoryRequest $request): JsonResponse
    {
        BlogCategory::create($request->validated());

        return response()->json(['message' => 'Blog Category created successfully.'], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogCategoryRequest $request
     * @param BlogCategory $blogCategory
     * @return JsonResponse
     */
    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->update($request->validated());

        return response()->json(['message' => 'Blog Category updated successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BlogCategory $blogCategory
     * @return JsonResponse
     */
    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->delete();

        return response()->json(['message' => 'Blog Category deleted successfully.'], status: Response::HTTP_OK);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BlogCategoryRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BlogCategoryRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        BlogCategory::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_blog_categories',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' blog categories']
            ],
            subjectId: null,
            subjectType: \App\Models\BlogCategory::class
        );

        return response()->json(['message' => 'Blog Categories deleted successfully.'], Response::HTTP_OK);
    }
}
