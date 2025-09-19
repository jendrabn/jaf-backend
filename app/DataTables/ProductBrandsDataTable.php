<?php

namespace App\DataTables;

use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductBrandsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.productBrands.partials.action')
            ->setRowId('id')
            ->editColumn('logo', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank">
                        <img src="%s" alt="" class="theme-avatar border border-2 border-primary rounded">
                    </a>',
                    $row->logo?->url,
                    $row->logo?->preview_url
                );
            })
            ->rawColumns(['action', 'logo']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductBrand $model): QueryBuilder
    {
        return $model->newQuery()->select('product_brands.*')->withCount('products');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-productbrands')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Brand'),
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

            Column::computed('logo')
                ->title('LOGO'),

            Column::make('name')
                ->title('NAME'),

            Column::make('slug')
                ->title('SLUG')
                ->visible(false),

            Column::make('products_count')
                ->title('PRODUCTS COUNT'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
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
        return 'ProductBrands_' . date('dmY');
    }
}
