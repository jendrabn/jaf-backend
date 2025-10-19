<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CouponDataTable;
use App\DataTables\CouponUsageDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Product;
use App\Jobs\DeactivateCouponIfExpired;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function index(CouponDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.coupons.index');
    }

    public function create()
    {
        $products = Product::query()->published()->orderBy('id', 'desc')->get();

        return view('admin.coupons.create', compact('products'));
    }

    public function store(CouponRequest $request)
    {
        if ($request->promo_type == 'limit') {
            $coupon = Coupon::create([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'code' => $request->code_limit,
                'discount_type' => $request->discount_type_limit,
                'discount_amount' => $request->discount_amount_limit,
                'limit' => $request->limit,
                'limit_per_user' => $request->limit_per_user_limit,
                'start_date' => $request->start_date_limit,
                'end_date' => $request->end_date_limit,
            ]);
        } else if ($request->promo_type == 'period') {
            $coupon = Coupon::create([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'code' => $request->code_period,
                'limit_per_user' => $request->limit_per_user_period,
                'discount_type' => $request->discount_type_period,
                'discount_amount' => $request->discount_amount_period,
                'start_date' => $request->start_date_period,
                'end_date' => $request->end_date_period,
            ]);
        } else if ($request->promo_type == 'product') {
            $coupon = Coupon::create([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'discount_type' => $request->discount_type_product,
                'discount_amount' => $request->discount_amount_product,
                'start_date' => $request->start_date_product,
                'end_date' => $request->end_date_product,
            ]);

            $coupon->products()->attach($request->product_ids);
        }

        DB::afterCommit(function () use ($coupon) {
            if (in_array($coupon->promo_type, ['period', 'product'], true) && $coupon->end_date && $coupon->is_active) {
                \App\Jobs\DeactivateCouponIfExpired::dispatch($coupon->id)
                    ->onQueue('coupons')
                    ->delay(\Illuminate\Support\Carbon::parse($coupon->end_date));
            }
        });

        toastr('Coupon created successfully', 'success');

        return redirect()->route('admin.coupons.index');
    }

    public function show(CouponUsageDataTable $dataTable, Coupon $coupon)
    {
        $coupon->load('products');

        return $dataTable->with('coupon', $coupon)->render('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        $coupon->load('products');

        $products = Product::query()->published()->orderBy('id', 'desc')->get();

        if ($coupon->promo_type == 'limit') {
            $coupon->code_limit = $coupon->code;
            $coupon->limit_per_user_limit = $coupon->limit_per_user;
            $coupon->discount_type_limit = $coupon->discount_type;
            $coupon->discount_amount_limit = $coupon->discount_amount;
        } else if ($coupon->promo_type == 'period') {
            $coupon->code_period = $coupon->code;
            $coupon->limit_per_user_period = $coupon->limit_per_user;
            $coupon->discount_type_period = $coupon->discount_type;
            $coupon->discount_amount_period = $coupon->discount_amount;
            $coupon->start_date_period = $coupon->start_date;
            $coupon->end_date_period = $coupon->end_date;
        } else if ($coupon->promo_type == 'product') {
            $coupon->discount_type_product = $coupon->discount_type;
            $coupon->discount_amount_product = $coupon->discount_amount;
            $coupon->product_ids = $coupon->products->pluck('id')->toArray();
            $coupon->start_date_product = $coupon->start_date;
            $coupon->end_date_product = $coupon->end_date;
        }

        return view('admin.coupons.edit', compact('coupon', 'products'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        if ($request->promo_type == 'limit') {
            $coupon->update([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'code' => $request->code_limit,
                'discount_type' => $request->discount_type_limit,
                'discount_amount' => $request->discount_amount_limit,
                'limit' => $request->limit,
                'limit_per_user' => $request->limit_per_user_limit,
                'start_date' => $request->start_date_limit,
                'end_date' => $request->end_date_limit,
            ]);
        } else if ($request->promo_type == 'period') {
            $coupon->update([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'code' => $request->code_period,
                'limit_per_user' => $request->limit_per_user_period,
                'discount_type' => $request->discount_type_period,
                'discount_amount' => $request->discount_amount_period,
                'start_date' => $request->start_date_period,
                'end_date' => $request->end_date_period,
            ]);
        } else if ($request->promo_type == 'product') {
            $coupon->update([
                'name' => $request->name,
                'description' => $request->description,
                'promo_type' => $request->promo_type,
                'discount_type' => $request->discount_type_product,
                'discount_amount' => $request->discount_amount_product,
                'start_date' => $request->start_date_product,
                'end_date' => $request->end_date_product,
            ]);

            $coupon->products()->sync($request->product_ids);
        }

        DB::afterCommit(function () use ($coupon) {
            if (in_array($coupon->promo_type, ['period', 'product'], true) && $coupon->end_date && $coupon->is_active) {
                \App\Jobs\DeactivateCouponIfExpired::dispatch($coupon->id)
                    ->onQueue('coupons')
                    ->delay(\Illuminate\Support\Carbon::parse($coupon->end_date));
            }
        });

        toastr('Coupon updated successfully', 'success');

        return redirect()->route('admin.coupons.edit', $coupon->id);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response(null, 204);
    }

    public function massDestroy(Request $request)
    {
        $ids = $request->ids;
        $count = count($ids);

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        Coupon::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_coupons',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' coupons.'],
            ],
            subjectId: null,
            subjectType: \App\Models\Coupon::class
        );

        return response(null, 204);
    }
}
