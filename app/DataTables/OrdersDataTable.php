<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrdersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.orders.partials.action')
            ->addColumn('customer', function ($row) {
                if (! $row->user) {
                    return '';
                }

                return $row->user->name.'<a class="ml-2 text-muted small icon-btn" href="'.route('admin.users.show', $row->user->id).'"><i class="bi bi-box-arrow-up-right"></i></a>';
            })
            ->editColumn(
                'items',
                fn ($row) => view('admin.orders.partials.item', ['items' => $row->items])
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
                fn ($row) => formatRupiah($row->invoice->amount)
            )
            ->editColumn(
                'shipping',
                fn ($row) => $row->shipping ? strtoupper($row->shipping->courier).'/'.$row->shipping->tracking_number : ''
            )
            ->addColumn(
                'payment_method',
                fn ($row) => strtoupper($row->invoice->payment->method)
            )
            ->setRowId('id')
            ->rawColumns(['action', 'customer', 'items', 'status']);
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
            fn ($q) => $q->where('status', request('status'))
        );

        $model->when(
            request()->filled('daterange'),
            fn ($q) => $q->whereBetween('created_at', explode(' - ', request('daterange')))
        );

        $model->when(
            request()->filled('payment_method'),
            fn ($q) => $q->whereHas('invoice', fn ($q) => $q->whereHas('payment', fn ($q) => $q->where('method', request('payment_method'))))
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
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Deselect All'),
                Button::make('excel')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-file-earmark-excel me-1"></i> Excel'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap me-1"></i> Columns'),
                Button::make('bulkDelete')
                    ->className('btn btn-danger')
                    ->text('<i class="bi bi-trash3 me-1"></i> Delete Selected'),
                Button::make('filter')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-funnel me-1"></i> Filter'),
                Button::make('printInvoice')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-printer me-1"></i> Print Invoice'),
            ])
            ->ajax([
                'data' => '
                    function(d) {
                        $.each($("#form-filter").serializeArray(), function(key, val) {
                            d[val.name] = val.value;
                        })

                        let status = $("#nav-pills-status .nav-link.active").data("status");
                        d["status"] = status;
                    }',
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
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::make('invoice.number', 'invoice.number')
                ->title('INVOICE')
                ->visible(false),

            Column::make('customer', 'user.name')
                ->title('CUSTOMER'),

            Column::make('items', 'items.name')
                ->title('PRODUCT(S)')
                ->orderable(false),

            Column::make('amount', 'invoice.amount')
                ->title('AMOUNT'),

            Column::make('payment_method', 'invoice.payment.method')
                ->title('PAYMENT METHOD'),

            Column::make('shipping', 'shipping.tracking_number')
                ->title('SHIPPING'),

            Column::make('status')
                ->title('STATUS'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false),

            Column::make('confirmed_at')
                ->title('DATE & TIME CONFIRMED')
                ->visible(false),

            Column::make('completed_at')
                ->title('DATE & TIME COMPLETED')
                ->visible(false),

            Column::make('cancelled_at')
                ->title('DATE & TIME CANCELLED')
                ->visible(false),

            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Orders_'.date('dmY');
    }
}
