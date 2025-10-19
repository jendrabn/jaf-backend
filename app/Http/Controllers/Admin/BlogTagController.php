<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogTagsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTagRequest;
use App\Models\BlogTag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BlogTagController extends Controller
{
    public function index(BlogTagsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogTags.index');
    }

    public function create(): View
    {
        return view('admin.blogTags.partials.modal', [
            'mode' => 'create',
            'tag' => null,
            'action' => route('admin.blog-tags.store'),
            'method' => 'POST',
            'title' => 'Create Blog Tag',
        ]);
    }

    public function store(BlogTagRequest $request): JsonResponse
    {
        BlogTag::create($request->validated());

        return response()->json(['message' => 'Blog Tag created successfully.'], Response::HTTP_CREATED);
    }

    public function edit(BlogTag $blogTag): View
    {
        return view('admin.blogTags.partials.modal', [
            'mode' => 'edit',
            'tag' => $blogTag,
            'action' => route('admin.blog-tags.update', $blogTag),
            'method' => 'PUT',
            'title' => 'Edit Blog Tag',
        ]);
    }

    public function update(BlogTagRequest $request, BlogTag $blogTag): JsonResponse
    {
        $blogTag->update($request->validated());

        return response()->json(['message' => 'Blog Tag updated successfully.'], Response::HTTP_OK);
    }

    public function destroy(BlogTag $blogTag): JsonResponse
    {
        $blogTag->delete();

        return response()->json(['message' => 'Blog Tag deleted successfully.'], Response::HTTP_OK);
    }

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
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' blog tags.'],
            ],
            subjectId: null,
            subjectType: \App\Models\BlogTag::class
        );

        return response()->json(['message' => 'Blog Tag deleted successfully.'], Response::HTTP_OK);
    }
}
