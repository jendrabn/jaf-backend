@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Revenues</span>
                    <span class="info-box-number">{{ formatRupiah($total_revenues) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ $total_users }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-users-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Admin</span>
                    <span class="info-box-number">{{ $total_admin }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Categories</span>
                    <span class="info-box-number">{{ $total_categories }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Brands</span>
                    <span class="info-box-number">{{ $total_brands }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-bag-shopping"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Products</span>
                    <span class="info-box-number">{{ $total_products }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-clipboard"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Orders</span>
                    <span class="info-box-number">{{ $total_orders }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-image"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Banners</span>
                    <span class="info-box-number">{{ $total_banners }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payment Banks</span>
                    <span class="info-box-number">{{ $total_payment_banks }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payment E-Wallets</span>
                    <span class="info-box-number">{{ $total_payment_ewallets }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title">Revenues</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-revenue"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title">Orders</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-orders-count"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"
          rel="stylesheet"
          type="text/css" />
@endsection

@section('scripts')
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        $(function() {

            const ordersCount = {!! $orders_count !!};

            new Chart(document.getElementById('chart-orders-count').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ordersCount.map(item => item.status),
                    datasets: [{
                        label: 'Orders Count',
                        data: ordersCount.map(item => item.total)
                    }],
                    borderWidth: 1

                }
            });

            const revenues = {!! $revenues !!};

            new Chart(document.getElementById('chart-revenue').getContext('2d'), {
                type: 'line',
                data: {
                    labels: revenues.map(item => item.month_year),
                    datasets: [{
                        label: 'Total Revenue',
                        data: revenues.map(item => item.revenue)
                    }, {
                        label: 'Total Orders',
                        data: revenues.map(item => item.total)
                    }],
                    borderWidth: 1
                }
            })
        });
    </script>
@endsection
