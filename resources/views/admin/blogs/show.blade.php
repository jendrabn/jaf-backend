@extends('layouts.admin')

@section('page_title', 'Blog Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Blog' => route('admin.blogs.index'),
            'Blog Detail' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <div class="card-tools">
                        <a class="btn btn-default"
                           href="{{ route('admin.blogs.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>FEATURED IMAGE</th>
                                    <td class="text-center">
                                        <a href="{{ $blog->featured_image?->url }}"
                                           target="_blank">
                                            <img class="border border-2 border-primary rounded p-1"
                                                 src="{{ $blog->featured_image?->url }}"
                                                 style="max-width: 500px;" />
                                        </a>
                                        <br />

                                        <p class="mb-0 text-muted">{{ $blog->featured_image_description }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>TITLE</th>
                                    <td>{{ $blog->title }}</td>
                                </tr>

                                <tr>
                                    <th>SLUG</th>
                                    <td>{{ $blog->slug }}</td>
                                </tr>

                                <tr>
                                    <th>AUTHOR</th>
                                    <td>{{ $blog->author?->name }}</td>
                                </tr>

                                <tr>
                                    <th>CATEGORY NAME</th>
                                    <td>{{ $blog->category->name }}</td>
                                </tr>

                                <tr>
                                    <th>TAG(S)</th>
                                    <td>{{ $blog->tags->pluck('name')->implode(', ') }}</td>
                                </tr>

                                <tr>
                                    <th>MIN. READ</th>
                                    <td>{{ $blog->min_read }}</td>
                                </tr>

                                <tr>
                                    <th>CONTENT</th>
                                    <td>{!! $blog->content !!}</td>
                                </tr>

                                <tr>
                                    <th>VIEWS</th>
                                    <td>{{ $blog->views_count }}</td>
                                </tr>

                                <tr>
                                    <th>PUBLISHED</th>
                                    <td>{{ $blog->is_publish ? 'Yes' : 'No' }}</td>
                                </tr>

                                <tr>
                                    <th>DATE & TIME CREATED</th>
                                    <td>{{ $blog->created_at }}</td>
                                </tr>

                                <tr>
                                    <th>DATE & TIME UPDATED</th>
                                    <td>{{ $blog->updated_at }}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
