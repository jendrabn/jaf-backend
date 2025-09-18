@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        {{-- Total Revenues --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-primary inset-soft">
                    <i class="bi bi-currency-dollar fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Revenues</div>
                    <div class="font-weight-bold fs-125">{{ formatRupiah($total_revenues) }}</div>
                </div>
            </div>
        </div>

        {{-- Total Users --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-purple inset-soft">
                    <i class="bi bi-people fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Users</div>
                    <div class="font-weight-bold fs-125">{{ $total_users }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.users.index') }}"
                   title="Kelola Users">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Admin --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-teal inset-soft">
                    <i class="bi bi-people fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Admin</div>
                    <div class="font-weight-bold fs-125">{{ $total_admin }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.users.index') }}"
                   title="Kelola Admin">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Categories --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-warning inset-soft">
                    <i class="bi bi-folder fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Categories</div>
                    <div class="font-weight-bold fs-125">{{ $total_categories }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.product-categories.index') }}"
                   title="Kelola Categories">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Brands --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-warning inset-soft">
                    <i class="bi bi-folder2-open fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Brands</div>
                    <div class="font-weight-bold fs-125">{{ $total_brands }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.product-brands.index') }}"
                   title="Kelola Brands">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Products --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-pink inset-soft">
                    <i class="bi bi-bag fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Products</div>
                    <div class="font-weight-bold fs-125">{{ $total_products }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.products.index') }}"
                   title="Kelola Products">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-cyan inset-soft">
                    <i class="bi bi-box-seam fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Orders</div>
                    <div class="font-weight-bold fs-125">{{ $total_orders }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.orders.index') }}"
                   title="Kelola Orders">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Banners --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-indigo inset-soft">
                    <i class="bi bi-image fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Banners</div>
                    <div class="font-weight-bold fs-125">{{ $total_banners }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.banners.index') }}"
                   title="Kelola Banners">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Payment Banks --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-dark inset-soft">
                    <i class="bi bi-wallet2 fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Payment Banks</div>
                    <div class="font-weight-bold fs-125">{{ $total_payment_banks }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.banks.index') }}"
                   title="Kelola Banks">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Payment E-Wallets --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-dark inset-soft">
                    <i class="bi bi-wallet2 fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Payment E-Wallets</div>
                    <div class="font-weight-bold fs-125">{{ $total_payment_ewallets }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.ewallets.index') }}"
                   title="Kelola E-Wallets">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Coupons --}}
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="stat-card shadow-sm d-flex align-items-center card-surface p-16-18">
                <div
                     class="stat-icon d-flex align-items-center justify-content-center text-white mr-3 icon-52 bg-grad-fucshia inset-soft">
                    <i class="bi bi-gift fs-24"></i>
                </div>
                <div class="stat-body">
                    <div class="text-uppercase text-muted fs-085 ls-04">Total Coupons</div>
                    <div class="font-weight-bold fs-125">{{ $total_coupons }}</div>
                </div>
                <a class="ml-auto d-flex align-items-center text-secondary"
                   href="{{ route('admin.coupons.index') }}"
                   title="Kelola Coupons">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="row mt-12">
        <div class="col-lg-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-b-soft">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-graph-up-arrow mr-2"></i>
                        <h3 class="card-title mb-0">Revenue</h3>
                    </div>

                    {{-- Granularitas saja --}}
                    <div class="btn-group btn-group-toggle"
                         data-toggle="buttons"
                         id="grainToggle">
                        <label class="btn btn-sm btn-outline-secondary {{ $default_grain === 'day' ? 'active' : '' }}">
                            <input {{ $default_grain === 'day' ? 'checked' : '' }}
                                   data-grain="day"
                                   type="radio"> Daily
                        </label>
                        <label class="btn btn-sm btn-outline-secondary {{ $default_grain === 'week' ? 'active' : '' }}">
                            <input {{ $default_grain === 'week' ? 'checked' : '' }}
                                   data-grain="week"
                                   type="radio"> Weeekly
                        </label>
                        <label class="btn btn-sm btn-outline-secondary {{ $default_grain === 'month' ? 'active' : '' }}">
                            <input {{ $default_grain === 'month' ? 'checked' : '' }}
                                   data-grain="month"
                                   type="radio"> Monthly
                        </label>
                        <label class="btn btn-sm btn-outline-secondary {{ $default_grain === 'year' ? 'active' : '' }}">
                            <input {{ $default_grain === 'year' ? 'checked' : '' }}
                                   data-grain="year"
                                   type="radio"> Yearly
                        </label>
                    </div>
                </div>

                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="chart-revenue"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-12">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex align-items-center border-b-soft">
                    <i class="bi bi-bar-chart-line mr-2"></i>
                    <h3 class="card-title mb-0">Orders</h3>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="chart-orders-count"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const revenuesDict = @json($revenues_series); // {day:[{label,revenue,total}], week:[], ...}
            const currentGrain = @json($default_grain ?? 'week');

            const ordersCount = {!! $orders_count !!};

            // Orders (bar)
            new Chart(document.getElementById('chart-orders-count').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ordersCount.map(function(item) {
                        return item.status;
                    }),
                    datasets: [{
                        label: 'Orders Count',
                        data: ordersCount.map(function(item) {
                            return item.total;
                        })
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Revenue (line dashed)
            const ctx = document.getElementById('chart-revenue').getContext('2d');
            const chartRevenue = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                            label: 'Total Revenue',
                            data: [],
                            fill: false,
                            tension: 0,
                            borderWidth: 2,
                            borderDash: [8, 4],
                            pointRadius: 3.5,
                            pointHoverRadius: 5,
                            pointHitRadius: 8,
                            pointBorderWidth: 2,
                            yAxisID: 'yRupiah'
                        },
                        {
                            label: 'Total Orders',
                            data: [],
                            fill: false,
                            tension: 0,
                            borderWidth: 2,
                            borderDash: [4, 4],
                            pointRadius: 3.5,
                            pointHoverRadius: 5,
                            pointHitRadius: 8,
                            pointBorderWidth: 2,
                            yAxisID: 'yCount'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    spanGaps: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        },
                        yRupiah: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    return 'Rp ' + Number(v).toLocaleString('id-ID');
                                }
                            }
                        },
                        yCount: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    let label = ctx.dataset.label || '';
                                    let val = ctx.parsed.y;
                                    if (ctx.dataset.yAxisID === 'yRupiah') return label + ': Rp ' + Number(
                                        val).toLocaleString('id-ID');
                                    return label + ': ' + Number(val).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            function applySeries(grain) {
                let rows = revenuesDict[grain] || [];
                chartRevenue.data.labels = rows.map(function(r) {
                    return r.label;
                });
                chartRevenue.data.datasets[0].data = rows.map(function(r) {
                    return r.revenue;
                });
                chartRevenue.data.datasets[1].data = rows.map(function(r) {
                    return r.total;
                });

                // atur batas ticks X agar nyaman per-grain
                let maxTicks = (grain === 'day') ? 15 : (grain === 'week' ? 12 : (grain === 'month' ? 12 : 10));
                chartRevenue.options.scales.x.ticks.maxTicksLimit = maxTicks;

                chartRevenue.update();
            }

            // toggle granularitas
            document.querySelectorAll('#grainToggle input[type="radio"]').forEach(function(el) {
                el.addEventListener('change', function() {
                    currentGrain = this.getAttribute('data-grain');
                    applySeries(currentGrain);
                });
            });

            // init
            applySeries(currentGrain);
        })();
    </script>
@endsection
