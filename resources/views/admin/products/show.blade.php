@extends('layouts.admin')

@section('page_title', 'Product Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product' => route('admin.products.index'),
            'Product Detail' => null,
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
                           href="{{ route('admin.products.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $product->id }}</td>
                        </tr>

                        <tr>
                            <th>PRODUCT NAME</th>
                            <td>{{ $product->name }}</td>
                        </tr>

                        <tr>
                            <th>SLUG</th>
                            <td>{{ $product->slug }}</td>
                        </tr>

                        <tr>
                            <th>IMAGES</th>
                            <td>
                                @foreach ($product->images as $image)
                                    <a href="{{ $image->url }}"
                                       target="_blank">
                                        <img class="border border-2 border-primary rounded p-1"
                                             src="{{ $image->preview_url }}"
                                             style="width: 100px; height: 100px; object-fit: cover" />
                                    </a>
                                @endforeach
                            </td>
                        </tr>

                        <tr>
                            <th>CATEGORY</th>
                            <td>{{ $product->category?->name }}</td>
                        </tr>

                        <tr>
                            <th>BRAND</th>
                            <td>{{ $product->brand?->name }}</td>
                        </tr>

                        <tr>
                            <th>WEIGHT</th>
                            <td>{{ $product->weight }} gram / {{ round($product->weight / 1000, 2) }} kg</td>
                        </tr>

                        <tr>
                            <th>PRICE</th>
                            <td>@Rp($product->price)</td>
                        </tr>

                        <tr>
                            <th>GENDER</th>
                            <td>{{ $product->sex_label }}</td>
                        </tr>

                        <tr>
                            <th>DESCRIPTION</th>
                            <td>{!! $product->description !!}</td>
                        </tr>

                        <tr>
                            <th>PUBLISHED</th>
                            <td>
                                <input {{ $product->is_publish ? 'checked' : '' }}
                                       onclick="return false;"
                                       type="checkbox" />
                            </td>
                        </tr>

                        <tr>
                            <th>DATE & TIME CREATED</th>
                            <td>{{ $product->created_at }}</td>
                        </tr>

                        <tr>
                            <th>DATE & TIME UPDATED</th>
                            <td>{{ $product->updated_at }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
