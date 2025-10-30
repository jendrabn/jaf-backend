<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Ewallet;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        $now = now();

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
        $total_revenues = (int) (Order::where('status', Order::STATUS_COMPLETED)
            ->selectRaw('SUM(total_price + COALESCE(shipping_cost,0)) AS revenue')
            ->value('revenue') ?? 0);

        $orders_count = Order::selectRaw('COUNT(*) AS total, status')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => Str::title(str_replace('_', ' ', $item->status)),
                    'total' => (int) $item->total,
                ];
            });

        $dayRows = $this->dailyRevenueSeries($now);
        $weekRows = $this->weeklyRevenueSeries($now);
        $monthRows = $this->monthlyRevenueSeries($now);
        $yearRows = $this->yearlyRevenueSeries($now);

        $revenues_series = [
            'day' => $dayRows,
            'week' => $weekRows,
            'month' => $monthRows,
            'year' => $yearRows,
        ];

        $default_grain = 'week';

        $recent_orders = Order::query()
            ->with([
                'user:id,name',
                'invoice:id,order_id,amount',
                'invoice.payment:id,invoice_id,method',
            ])
            ->select(['id', 'user_id', 'total_price', 'shipping_cost', 'status', 'created_at'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recent_contact_messages = ContactMessage::query()
            ->with(['handler:id,name'])
            ->select(['id', 'name', 'email', 'status', 'handled_by', 'created_at'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recent_audit_logs = AuditLog::query()
            ->with(['user:id,name'])
            ->select(['id', 'description', 'event', 'user_id', 'created_at'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
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
            'default_grain',
            'recent_orders',
            'recent_contact_messages',
            'recent_audit_logs'
        ));
    }

    private function dailyRevenueSeries(Carbon $now): Collection
    {
        return Order::selectRaw('
                DATE(created_at) AS day,
                SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                COUNT(*) AS total
            ')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', $now->copy()->subDays(30)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                $date = Carbon::parse($row->day);

                return [
                    'label' => $date->format('d M'),
                    'revenue' => (int) $row->revenue,
                    'total' => (int) $row->total,
                    'sort' => $date->timestamp,
                ];
            })->values();
    }

    private function weeklyRevenueSeries(Carbon $now): Collection
    {
        $startDate = $now->copy()->subWeeks(26)->startOfWeek();

        if ($this->usesSqlite()) {
            $rows = Order::selectRaw("
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total,
                    strftime('%Y', created_at) AS iso_year,
                    printf('%02d', (strftime('%W', created_at) + 1)) AS iso_week
                ")
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('iso_year', 'iso_week')
                ->orderBy('iso_year')
                ->orderBy('iso_week')
                ->get();
        } else {
            $rows = Order::selectRaw("
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total,
                    DATE_FORMAT(created_at, '%x') AS iso_year,
                    DATE_FORMAT(created_at, '%v') AS iso_week
                ")
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('iso_year', 'iso_week')
                ->orderBy('iso_year')
                ->orderBy('iso_week')
                ->get();
        }

        $reference = $now->copy();

        return $rows->map(function ($row) use ($reference) {
            $isoWeek = (int) $row->iso_week;
            $isoWeek = max(1, min(53, $isoWeek));

            $start = $reference->copy()->setISODate((int) $row->iso_year, $isoWeek)->startOfWeek();
            $end = $start->copy()->endOfWeek();

            return [
                'label' => $start->format('d M') . '–' . $end->format('d M Y'),
                'revenue' => (int) $row->revenue,
                'total' => (int) $row->total,
                'sort' => $start->timestamp,
            ];
        })->values();
    }

    private function monthlyRevenueSeries(Carbon $now): Collection
    {
        $startDate = $now->copy()->subMonths(12)->startOfMonth();

        if ($this->usesSqlite()) {
            $rows = Order::selectRaw("
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total,
                    strftime('%Y', created_at) AS y,
                    strftime('%m', created_at) AS m
                ")
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('y', 'm')
                ->orderBy('y')
                ->orderBy('m')
                ->get();
        } else {
            $rows = Order::selectRaw('
                    YEAR(created_at) AS y,
                    MONTH(created_at) AS m,
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total
                ')
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('y', 'm')
                ->orderBy('y')
                ->orderBy('m')
                ->get();
        }

        return $rows->map(function ($row) {
            $date = Carbon::createFromDate((int) $row->y, (int) $row->m, 1);

            return [
                'label' => $date->format('M Y'),
                'revenue' => (int) $row->revenue,
                'total' => (int) $row->total,
                'sort' => $date->timestamp,
            ];
        })->values();
    }

    private function yearlyRevenueSeries(Carbon $now): Collection
    {
        $startDate = $now->copy()->subYears(5)->startOfYear();

        if ($this->usesSqlite()) {
            $rows = Order::selectRaw("
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total,
                    strftime('%Y', created_at) AS y
                ")
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('y')
                ->orderBy('y')
                ->get();
        } else {
            $rows = Order::selectRaw('
                    YEAR(created_at) AS y,
                    SUM(total_price + COALESCE(shipping_cost,0)) AS revenue,
                    COUNT(*) AS total
                ')
                ->where('status', Order::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->groupBy('y')
                ->orderBy('y')
                ->get();
        }

        return $rows->map(function ($row) {
            $date = Carbon::createFromDate((int) $row->y, 1, 1);

            return [
                'label' => $date->format('Y'),
                'revenue' => (int) $row->revenue,
                'total' => (int) $row->total,
                'sort' => $date->timestamp,
            ];
        })->values();
    }

    private function usesSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
