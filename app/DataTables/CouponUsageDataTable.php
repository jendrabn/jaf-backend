<?php

namespace App\DataTables;

use App\Models\CouponUsage;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CouponUsageDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'couponusage.action')
            ->addColumn('order_id', function ($row) {
                return $row->order->id . '<a href="' . route('admin.orders.show', $row->order->id) . '"><i class="bi bi-box-arrow-up-right"></i></a>';
            })
            ->addColumn('customer', function ($row) {
                return $row->order->user->name . '<a href="' . route('admin.users.show', $row->order->user->id) . '"><i class="bi bi-box-arrow-up-right"></i></a>';
            })
            ->setRowId('id')
            ->rawColumns(['action', 'order_id', 'customer']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CouponUsage $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['coupon', 'order', 'order.user'])
            ->where('coupon_id', $this->coupon->id);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('datatable-couponusage')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->buttons([]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->title('ID'),

            Column::make('order_id', 'order.id')
                ->title('ORDER ID'),

            Column::make('customer', 'order.user.name')
                ->title('CUSTOMER'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CouponUsage_' . date('YmdHis');
    }
}
