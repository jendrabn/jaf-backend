<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use App\Traits\MediaUploadingTrait;
use App\Traits\QuillUploadImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    use MediaUploadingTrait;
    use QuillUploadImage;

    /**
     * Display a listing of the resources.
     */
    public function index(BlogsDataTable $dataTable): mixed
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('All', null);
        $tags = BlogTag::pluck('name', 'id')->prepend('All', null);
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('All', null);

        return $dataTable->render('admin.blogs.index', compact(
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('---', null);
        $tags = BlogTag::pluck('name', 'id');
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('---', null);

        return view('admin.blogs.create', compact(
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request): RedirectResponse
    {
        $blog = Blog::create($request->validated());

        $blog->tags()->attach($request->validated('tag_ids'));

        if ($request->input('featured_image', false)) {
            $path = storage_path('tmp/uploads/' . basename($request->input('featured_image')));
            $blog->addMedia($path)->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $blog->id]);
        }

        toastr('Blog created successfully', 'success');

        return redirect()->route('admin.blogs.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): View
    {
        return view('admin.blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog): View
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('---', null);
        $tags = BlogTag::pluck('name', 'id');
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('---', null);

        return view('admin.blogs.edit', compact(
            'blog',
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Updates the specified resource in storage.
     */
    public function update(BlogRequest $request, Blog $blog): RedirectResponse
    {
        $blog->update($request->validated());
        $blog->tags()->sync($request->validated('tag_ids'));

        if ($request->input('featured_image', false)) {
            if (! $blog->featured_image || $request->input('featured_image') !== $blog->featured_image->file_name) {
                if ($blog->featured_image) {
                    $blog->featured_image->delete();
                }

                $path = storage_path('tmp/uploads/' . basename($request->input('featured_image')));
                $blog->addMedia($path)->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
            }
        } elseif ($blog->featured_image) {
            $blog->featured_image->delete();
        }

        toastr('Blog updated successfully', 'success');

        return back();
    }

    /**
     * Removes the specified resource from storage.
     */
    public function destroy(Blog $blog): JsonResponse
    {
        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Removes the specified resources from storage.
     */
    public function massDestroy(BlogRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Blog::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_blogs',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' blogs'],
            ],
            subjectId: null,
            subjectType: \App\Models\Blog::class
        );

        return response()->json(['message' => 'Blogs deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Store a newly uploaded media in storage using CKEditor.
     *
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        return $this->quillUploadImage($request);
    }

    public function storeCKEditorImages(Request $request)
    {
        $model = new Blog;
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json([
            'filename' => $media->file_name,
            'uploaded' => 1,
            'url' => $media->getUrl(),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function published(Blog $blog): JsonResponse
    {
        $blog->update(['is_publish' => ! $blog->is_publish]);

        return response()->json(['message' => 'Blog updated successfully.'], Response::HTTP_OK);
    }
}
