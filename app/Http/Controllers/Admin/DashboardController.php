<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Ewallet;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(): View
    {
        $total_admin = User::role(User::ROLE_ADMIN)->count();
        $total_users = User::role(User::ROLE_USER)->count();
        $total_categories = ProductCategory::count();
        $total_brands = ProductBrand::count();
        $total_products = Product::count();
        $total_orders = Order::count();
        $total_banners = Banner::count();
        $total_payment_banks = Bank::count();
        $total_payment_ewallets = Ewallet::count();
        $total_coupons = Coupon::active()->count();
        $total_revenues = Order::selectRaw('sum(total_price + shipping_cost) as revenue')
            ->where('status', Order::STATUS_COMPLETED)
            ->first()
            ->revenue;

        $orders_count = Order::selectRaw('count(*) as total, status')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => Str::title(implode(' ', explode('_', $item->status))),
                    'total' => $item->total,
                ];
            });

        // ====== REVENUE SERIES (empat granularitas) ======
        // - Harian: 30 hari terakhir
        // - Mingguan: 26 minggu terakhir
        // - Bulanan: 12 bulan terakhir
        // - Tahunan: 5 tahun terakhir

        // 1) Harian (30 hari)
        $dayRows = Order::selectRaw('
                DATE(created_at) as d,
                SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                COUNT(*) AS total
            ')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('d')
            ->get()
            ->map(function ($r) {
                $d = Carbon::parse($r->d);

                return [
                    'label' => $d->format('d M'),
                    'revenue' => (int) $r->revenue,
                    'total' => (int) $r->total,
                    'sort' => $d->timestamp,
                ];
            })->values();

        // 2) Mingguan ISO (26 minggu)
        $weekRows = Order::selectRaw("
                SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                COUNT(*) AS total,
                DATE_FORMAT(created_at, '%x') AS iso_year,  -- ISO year
                DATE_FORMAT(created_at, '%v') AS iso_week   -- ISO week
            ")
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subWeeks(26)->startOfWeek())
            ->groupBy('iso_year', 'iso_week')
            ->orderBy('iso_year')
            ->orderBy('iso_week')
            ->get()
            ->map(function ($r) {
                $start = Carbon::now()->setISODate((int) $r->iso_year, (int) $r->iso_week)->startOfWeek(); // Mon
                $end = (clone $start)->endOfWeek(); // Sun
                $label = $start->format('d M').'–'.$end->format('d M Y');

                return [
                    'label' => $label,
                    'revenue' => (int) $r->revenue,
                    'total' => (int) $r->total,
                    'sort' => $start->timestamp,
                ];
            })->values();

        // 3) Bulanan (12 bulan)
        $monthRows = Order::selectRaw('
                YEAR(created_at) AS y,
                MONTH(created_at) AS m,
                SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                COUNT(*) AS total
            ')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get()
            ->map(function ($r) {
                $d = Carbon::createFromDate($r->y, $r->m, 1);

                return [
                    'label' => $d->format('M Y'),
                    'revenue' => (int) $r->revenue,
                    'total' => (int) $r->total,
                    'sort' => $d->timestamp,
                ];
            })->values();

        // 4) Tahunan (5 tahun)
        $yearRows = Order::selectRaw('
                YEAR(created_at) AS y,
                SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                COUNT(*) AS total
            ')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subYears(5)->startOfYear())
            ->groupBy('y')
            ->orderBy('y')
            ->get()
            ->map(function ($r) {
                $d = Carbon::createFromDate($r->y, 1, 1);

                return [
                    'label' => $d->format('Y'),
                    'revenue' => (int) $r->revenue,
                    'total' => (int) $r->total,
                    'sort' => $d->timestamp,
                ];
            })->values();

        $revenues_series = [
            'day' => $dayRows,
            'week' => $weekRows,
            'month' => $monthRows,
            'year' => $yearRows,
        ];

        // default tampilan: Mingguan
        $default_grain = 'week';

        return view(
            'admin.dashboard',
            compact(
                'total_admin',
                'total_users',
                'total_categories',
                'total_brands',
                'total_products',
                'total_orders',
                'total_banners',
                'total_payment_banks',
                'total_payment_ewallets',
                'total_coupons',
                'total_revenues',
                'orders_count',
                'revenues_series',
                'default_grain'
            )
        );
    }
}
