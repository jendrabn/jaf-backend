<?php

namespace App\DataTables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.coupons.partials.action')
            ->setRowId('id')
            ->editColumn('promo_type', function ($coupon) {
                return strtoupper($coupon->promo_type);
            })
            ->editColumn('discount_amount', function ($coupon) {
                if ($coupon->discount_type == 'fixed') {
                    return formatRupiah($coupon->discount_amount) . '(Flat)';
                } else if ($coupon->discount_type == 'percentage') {
                    return $coupon->discount_amount . '%';
                } else {
                    return null;
                }
            })
            ->editColumn('is_active', function ($coupon) {
                return $coupon->is_active ? 'Active' : 'Expired';
            })
            ->rawColumns(['action', 'is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Coupon $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('usages');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('datatable-coupon')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->buttons([
                Button::make('create')
                    ->text('Create Coupon'),
                Button::make('selectAll')
                    ->text('Select All'),
                Button::make('selectNone')
                    ->text('Deselect All'),
                Button::make('excel')
                    ->text('Excel'),
                Button::make('colvis')
                    ->text('Columns'),
                Button::make('bulkDelete')
                    ->text('Delete Selected'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::checkbox('&nbsp;')
                ->exportable(false)
                ->printable(false)
                ->width(30),

            Column::make('id')
                ->title('ID')
                ->width(30),

            Column::make('name')
                ->title('NAME'),

            Column::make('code')
                ->title('CODE'),

            Column::make('discount_amount')
                ->title('DISCOUNT (%/FLAT)'),

            Column::make('promo_type')
                ->title('PROMO TYPE'),

            Column::make('limit')
                ->title('LIMIT'),

            Column::make('limit_per_user')
                ->title('LIMIT PER USER'),

            Column::make('start_date')
                ->title('VALID FROM'),

            Column::make('end_date')
                ->title('VALID UNTIL'),

            Column::computed('available_coupons')
                ->title('AVAILABLE COUPONS'),

            Column::make('is_active')
                ->title('STATUS'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false),

            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false)
                ->width(60),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Coupon_' . date('YmdHis');
    }
}
