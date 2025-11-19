@extends('layouts.admin')

@section('page_title', 'Create Flash Sale')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Flash Sales' => route('admin.flash-sales.index'),
            'Create' => null,
        ],
    ])
@endsection

@section('content')
    @php
        $flashSaleModel = $flashSale ?? null;
        $initialProductRows = old('products', $productRows ?? []);
        if (empty($initialProductRows)) {
            $initialProductRows = [[]];
        }
    @endphp

    <form action="{{ route('admin.flash-sales.store') }}"
          method="POST">
        @csrf

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-lg">
                    <div class="card-header border-bottom-0">
                        <div class="card-tools">
                            <a class="btn btn-default"
                               href="{{ route('admin.flash-sales.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to
                                list</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="required"
                                   for="name">Event Name</label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   type="text"
                                   value="{{ old('name', $flashSaleModel?->name ?? '') }}">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3">{{ old('description', $flashSaleModel?->description ?? '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="required"
                                   for="flash-sale-range">Schedule (Start - End)</label>
                            <input class="form-control @error('start_at') is-invalid @enderror @error('end_at') is-invalid @enderror"
                                   id="flash-sale-range"
                                   placeholder="Select start & end datetime"
                                   readonly
                                   type="text">
                            <input id="start_at"
                                   name="start_at"
                                   type="hidden"
                                   value="{{ old('start_at', optional($flashSaleModel?->start_at)->format('Y-m-d H:i:s')) }}">
                            <input id="end_at"
                                   name="end_at"
                                   type="hidden"
                                   value="{{ old('end_at', optional($flashSaleModel?->end_at)->format('Y-m-d H:i:s')) }}">
                            @error('start_at')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            @error('end_at')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="d-block">Active Status</label>
                            <div class="custom-control custom-switch">
                                <input name="is_active"
                                       type="hidden"
                                       value="0">
                                <input {{ old('is_active', $flashSaleModel?->is_active ?? true) ? 'checked' : '' }}
                                       class="custom-control-input"
                                       id="is_active"
                                       name="is_active"
                                       type="checkbox"
                                       value="1">
                                <label class="custom-control-label"
                                       for="is_active">Activate this flash sale event</label>
                            </div>
                            @error('is_active')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Products</h6>
                            <button class="btn btn-primary"
                                    id="add-product-row"
                                    type="button">
                                <i class="bi bi-plus-circle mr-1"></i>
                                Add Product
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle"
                                   id="flash-sale-products-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Product</th>
                                        <th style="width: 20%">Flash Price</th>
                                        <th style="width: 15%">Flash Stock</th>
                                        <th style="width: 15%">Max/User</th>
                                        <th style="width: 10%">&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($initialProductRows as $index => $row)
                                        @include('admin.flashSale.partials.product-row', [
                                            'index' => $index,
                                            'row' => $row,
                                            'productsList' => $productsList,
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('products')
                            <span class="text-danger small d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="card-footer d-flex justify-content-end border-top-0 gap-2">
                        <a class="btn btn-light"
                           href="{{ route('admin.coupons.index') }}">
                            <i class="bi bi-x-circle mr-1"></i>Cancel
                        </a>
                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-save mr-1"></i>
                            Save Flash Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script id="flash-sale-product-row-template"
                type="text/template">
            {!! view('admin.flashSale.partials.product-row', [
                'index' => '__INDEX__',
                'row' => [],
                'productsList' => $productsList,
                'isTemplate' => true,
            ])->render() !!}
        </script>
    </form>

    @include('admin.flashSale.partials.price-modal')
@endsection

@section('scripts')
    @include('admin.flashSale.partials.form-scripts', ['nextIndex' => count($initialProductRows)])
@endsection
