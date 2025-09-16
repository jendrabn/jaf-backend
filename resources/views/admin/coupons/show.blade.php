@extends('layouts.admin')

@section('page_title', 'Coupon Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product Coupons' => route('admin.coupons.index'),
            'Coupon Detail' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-striped table-bordered datatable ajaxTable']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
@endsection
