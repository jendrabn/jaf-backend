<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use App\DataTables\BlogTagsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTagRequest;
use Symfony\Component\HttpFoundation\Response;

class BlogTagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param BlogTagsDataTable $dataTable
     * @return mixed
     */
    public function index(BlogTagsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogTags.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogTagRequest $request
     * @return JsonResponse
     */
    public function store(BlogTagRequest $request): JsonResponse
    {
        BlogTag::create($request->validated());

        return response()->json(['message' => 'Blog Category created successfully.'], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogTagRequest $request
     * @param BlogTag $blogTag
     * @return JsonResponse
     */
    public function update(BlogTagRequest $request, BlogTag $blogTag): JsonResponse
    {
        $blogTag->update($request->validated());

        return response()->json(['message' => 'Blog Tag updated successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BlogTag $blogTag
     * @return JsonResponse
     */
    public function destroy(BlogTag $blogTag): JsonResponse
    {
        $blogTag->delete();

        return response()->json(['message' => 'Blog Tag deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BlogTagRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BlogTagRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        BlogTag::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_blog_tags',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' blog tags.'],
            ],
            subjectId: null,
            subjectType: \App\Models\BlogTag::class
        );

        return response()->json(['message' => 'Blog Tag deleted successfully.'], Response::HTTP_OK);
    }
}
