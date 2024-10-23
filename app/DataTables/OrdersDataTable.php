<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class OrdersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.orders.partials.action')
            ->editColumn(
                'items',
                fn($row) => view('admin.orders.partials.item', ['items' => $row->items])
            )
            ->editColumn('status', function ($row) {
                $status = Order::STATUSES[$row->status];

                return sprintf(
                    '<span class="badge badge-%s rounded-0">%s</span>',
                    $status['color'],
                    $status['label']
                );
            })
            ->editColumn(
                'amount',
                fn($row) => formatRupiah($row->invoice->amount)
            )
            ->editColumn(
                'shipping',
                fn($row) => $row->shipping ? strtoupper($row->shipping->courier) . '/' . $row->shipping->tracking_number : ''
            )
            ->setRowId('id')
            ->rawColumns(['action', 'items', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        $model = $model->newQuery()
            ->with([
                'user',
                'items',
                'items.product',
                'items.product.media',
                'invoice',
                'shipping',
                'invoice.payment',
            ])
            ->select('orders.*');

        $model->when(
            request()->filled('status'),
            fn($q) => $q->where('status', request('status'))
        );

        $model->when(
            request()->filled('daterange'),
            fn($q) => $q->whereBetween('created_at', explode(' - ', request('daterange')))
        );

        $model->when(
            request()->filled('payment_method'),
            fn($q) => $q->whereHas('invoice', fn($q) => $q->whereHas('payment', fn($q) => $q->where('method', request('payment_method'))))
        );

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-orders')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->buttons([
                Button::make('selectAll'),
                Button::make('selectNone'),
                Button::make('excel'),
                Button::make('reset'),
                Button::make('reload'),
                Button::make('colvis'),
                Button::make('bulkDelete'),
                Button::make('filter'),
                Button::make('printInvoice'),
            ])
            ->ajax([
                'data' => '
                    function(d) {
                        $.each($("#form-filter").serializeArray(), function(key, val) {
                            d[val.name] = val.value;
                        })

                        let status = $("#nav-pills-status .nav-link.active").data("status");
                        d["status"] = status;
                    }'
            ])
        ;
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
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::make('invoice.number', 'invoice.number')
                ->visible(false),

            Column::make('user.name', 'user.name')
                ->title('Buyer'),

            Column::make('items', 'items.name')
                ->title('Product(s)')
                ->orderable(false),

            Column::make('amount', 'invoice.amount'),

            Column::make('invoice.payment.method', 'invoice.payment.method')
                ->title('Payment Method')
                ->visible(false),

            Column::make('shipping', 'shipping.tracking_number'),

            Column::make('status'),

            Column::make('created_at')
                ->visible(false),

            Column::make('confirmed_at')
                ->visible(false),

            Column::make('completed_at')
                ->visible(false),

            Column::make('cancelled_at')
                ->visible(false),

            Column::computed('action', 'Action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Orders_' . date('dmY');
    }
}
