<?php

namespace App\DataTables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
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
                    return formatRupiah($coupon->discount_amount).'(Flat)';
                } elseif ($coupon->discount_type == 'percentage') {
                    return $coupon->discount_amount.'%';
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
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Coupon'),
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Deselect All'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise me-1"></i> Reload'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap me-1"></i> Columns'),
                Button::make('bulkDelete')
                    ->className('btn btn-danger')
                    ->text('<i class="bi bi-trash3 me-1"></i> Delete Selected'),
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
        return 'Coupon_'.date('YmdHis');
    }
}

