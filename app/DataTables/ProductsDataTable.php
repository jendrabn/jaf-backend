<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.products.partials.action')
            ->editColumn('image', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank">
                        <img src="%s" alt="" class="theme-avatar border border-2 border-primary rounded">
                    </a>',
                    $row->image?->url,
                    $row->image?->preview_url
                );
            })
            ->editColumn(
                'price',
                fn($row) => formatRupiah($row->price)
            )
            ->editColumn('is_publish', function ($row) {
                return sprintf(
                    '<input type="checkbox" onclick="return false" %s />',
                    $row->is_publish ? 'checked' : ''
                );
            })
            ->addColumn('product_info', function ($product) {
                return view('admin.products.partials.product-column', compact('product'));
            })
            ->setRowId('id')
            ->rawColumns(['action', 'image', 'is_publish', 'product_info']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        $model = $model->newQuery()
            ->with(['category', 'brand', 'media', 'productRatings', 'coupons'])
            ->select('products.*');

        $filter_keys = ['product_category_id', 'product_brand_id', 'sex', 'is_publish'];

        foreach ($filter_keys as $key) {
            $model->when(request()->filled($key), fn($q) => $q->where($key, request($key)));
        }

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-products')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Product'),
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Select None'),
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
            ])
            ->ajax([
                'data' =>
                'function (data) {
                        $.each($("#form-filter").serializeArray(), function (key, val) {
                           data[val.name] = val.value;
                        });
                    }'
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

            Column::make('product_info', 'name')
                ->title('PRODUCT'),

            Column::make('slug')
                ->title('SLUG')
                ->visible(false),

            Column::make('category.name', 'category.name')
                ->title('CATEGORY'),

            Column::make('brand.name', 'brand.name')
                ->title('BRAND'),

            Column::computed('sex_label')
                ->title('SEX')
                ->visible(false),

            Column::make('price')
                ->title('PRICE'),

            Column::make('stock')
                ->title('STOCK'),

            Column::make('weight')
                ->title('WEIGHT')
                ->visible(false),

            Column::computed('is_publish')
                ->title('PUBLISHED')
                ->visible(false),

            Column::make('sold_count')
                ->title('SALES')
                ->searchable(false),

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
        return 'Products_' . date('dmY');
    }
}
