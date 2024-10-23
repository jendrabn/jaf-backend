<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\User;
use App\Models\Order;
use App\Models\Banner;
use App\Models\Ewallet;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ProductCategory;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    /**
     * Show the admin dashboard.
     *
     * @return View
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
                    'total' => $item->total
                ];
            });

        $revenues = Order::selectRaw('
        sum(total_price + shipping_cost) as revenue,
        count(*) as total,
        MONTH(created_at) as month,
        YEAR(created_at) as year')
            ->where('status', Order::STATUS_COMPLETED)
            ->groupBy('month', 'year')
            ->orderByRaw('year desc, month desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month_year' => Carbon::createFromDate($item->year, $item->month, 1)->format('F Y'),
                    'revenue' => $item->revenue,
                    'total' => $item->total
                ];
            });

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
                'total_revenues',
                'orders_count',
                'revenues'
            )
        );
    }
}
