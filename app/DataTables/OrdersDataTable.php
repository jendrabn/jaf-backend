<?php

namespace App\DataTables;

use App\Enums\OrderStatus;
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
            ->addColumn('name', function ($row) {
                if (! $row->user) {
                    return null;
                }

                return str()->words($row->user->name, 2, '') . externalIconLink(route('admin.users.show', $row->user->id));
            })
            ->editColumn(
                'items',
                fn($row) => view('admin.orders.partials.item', ['items' => $row->items])
            )
            ->editColumn('status', function ($row) {
                $status = OrderStatus::from($row->status);

                return badgeLabel(strtoupper($status->label()), $status->color());
            })
            ->editColumn(
                'amount',
                fn($row) => formatIDR($row->invoice->amount)
            )
            ->editColumn(
                'shipping',
                fn($row) => $row->shipping ? strtoupper($row->shipping->courier) . ($row->shipping->tracking_number ? ' - ' . $row->shipping->tracking_number : '') : ''
            )
            ->addColumn(
                'payment_method',
                fn($row) => strtoupper($row->invoice->payment->method)
            )
            ->setRowId('id')
            ->rawColumns(['action', 'name', 'items', 'status']);
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
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'stateSave' => true,
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                'language' => [],
            ])
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all mr-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle mr-1"></i> Deselect All'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise mr-1"></i> Reload'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap mr-1"></i> Columns'),
                Button::make('bulkDelete')
                    ->className('btn btn-danger')
                    ->text('<i class="bi bi-trash3 mr-1"></i> Delete Selected'),
                Button::make('filter')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-funnel mr-1"></i> Filter'),
                Button::make('printInvoice')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-printer mr-1"></i> Print Invoice'),
            ])
            ->ajax([
                'data' => '
                    function(d) {
                        $.each($("#form-filter").serializeArray(), function(key, val) {
                            d[val.name] = val.value;
                        });
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
                ->printable(false),

            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::make('invoice.number', 'invoice.number')
                ->title('INVOICE')
                ->visible(false)
                ->orderable(false)
                ->searchable(true),

            Column::make('name', 'user.name')
                ->title('NAME')
                ->orderable(true)
                ->searchable(true),

            Column::make('items', 'items.name')
                ->title('PRODUCT(S)')
                ->orderable(false)
                ->searchable(false),

            Column::make('amount', 'invoice.amount')
                ->title('AMOUNT')
                ->orderable(true)
                ->searchable(false),

            Column::make('payment_method', 'invoice.payment.method')
                ->title('PAYMENT METHOD')
                ->orderable(false)
                ->searchable(true),

            Column::make('shipping', 'shipping.tracking_number')
                ->title('SHIPPING')
                ->visible(false)
                ->orderable(false)
                ->searchable(true),

            Column::make('status')
                ->title('STATUS')
                ->orderable(false)
                ->searchable(false),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('confirmed_at')
                ->title('DATE & TIME CONFIRMED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('completed_at')
                ->title('DATE & TIME COMPLETED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('cancelled_at')
                ->title('DATE & TIME CANCELLED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

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
        return 'Orders_' . date('dmY');
    }
}
