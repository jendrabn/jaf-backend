<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogCategoriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use App\Traits\MediaUploadingTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BlogCategoryController extends Controller
{
    use MediaUploadingTrait;

    public function index(BlogCategoriesDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogCategories.index');
    }

    public function create(): View
    {
        return view('admin.blogCategories.partials.modal', [
            'mode' => 'create',
            'category' => null,
            'action' => route('admin.blog-categories.store'),
            'method' => 'POST',
            'title' => 'Create Blog Category',
        ]);
    }

    public function store(BlogCategoryRequest $request): JsonResponse
    {
        BlogCategory::create($request->validated());

        return response()->json(['message' => 'Blog Category created successfully.'], Response::HTTP_CREATED);
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blogCategories.partials.modal', [
            'mode' => 'edit',
            'category' => $blogCategory,
            'action' => route('admin.blog-categories.update', $blogCategory),
            'method' => 'PUT',
            'title' => 'Edit Blog Category',
        ]);
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->update($request->validated());

        return response()->json(['message' => 'Blog Category updated successfully.'], Response::HTTP_OK);
    }

    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->delete();

        return response()->json(['message' => 'Blog Category deleted successfully.'], status: Response::HTTP_OK);
    }

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
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' blog categories'],
            ],
            subjectId: null,
            subjectType: \App\Models\BlogCategory::class
        );

        return response()->json(['message' => 'Blog Categories deleted successfully.'], Response::HTTP_OK);
    }
}
