<?php

namespace App\DataTables;

use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FlashSalesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.flashSale.partials.action')
            ->editColumn('start_at', fn (FlashSale $flashSale) => optional($flashSale->start_at)->format('d M Y H:i'))
            ->editColumn('end_at', fn (FlashSale $flashSale) => optional($flashSale->end_at)->format('d M Y H:i'))
            ->editColumn('status_label', fn (FlashSale $flashSale) => sprintf(
                '<span class="badge badge-%s">%s</span>',
                e($flashSale->status_color),
                e($flashSale->status_label)
            ))
            ->editColumn('is_active', fn (FlashSale $flashSale) => $flashSale->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>')
            ->editColumn('products_count', fn (FlashSale $flashSale) => $flashSale->products_count ?? 0)
            ->setRowId('id')
            ->rawColumns(['status_label', 'is_active', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(FlashSale $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('flash_sales.*')
            ->withCount('products');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-flash-sales')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->orderBy(4, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Flash Sale'),
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all mr-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle mr-1"></i> Deselect All'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-filetype-csv mr-1"></i> CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise mr-1"></i> Reload'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap mr-1"></i> Columns'),
                Button::make('bulkDelete')
                    ->className('btn btn-danger')
                    ->text('<i class="bi bi-trash3 mr-1"></i> Delete Selected'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, \Yajra\DataTables\Html\Column>
     */
    public function getColumns(): array
    {
        return [
            Column::checkbox('&nbsp;')
                ->exportable(false)
                ->printable(false),
            Column::make('id')
                ->title('ID')
                ->width(60)
                ->orderable(true)
                ->searchable(false),
            Column::make('name')
                ->title('EVENT NAME'),
            Column::make('products_count')
                ->title('PRODUCTS')
                ->searchable(false),
            Column::make('start_at')
                ->title('START AT')
                ->searchable(false),
            Column::make('end_at')
                ->title('END AT')
                ->searchable(false),
            Column::make('status_label')
                ->title('STATUS')
                ->orderable(false)
                ->searchable(false),
            Column::make('is_active')
                ->title('ACTIVE')
                ->searchable(false),
            Column::make('created_at')
                ->title('CREATED AT')
                ->visible(false)
                ->searchable(false),
            Column::make('updated_at')
                ->title('UPDATED AT')
                ->visible(false)
                ->searchable(false),
            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false)
                ->width(120),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'FlashSales_'.date('dmY');
    }
}
