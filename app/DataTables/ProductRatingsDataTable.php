<?php

namespace App\DataTables;

use App\Models\Product;
use App\Models\ProductRating;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductRatingsDataTable extends DataTable
{
    public ?Product $product = null;

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $product = $this->getProduct();

        return (new EloquentDataTable($query))
            ->addColumn('customer', function (ProductRating $rating) {
                $customer = $rating->orderItem?->order?->user;

                if (! $customer) {
                    return '-';
                }

                return sprintf(
                    '%s <a href="%s"><i class="bi bi-box-arrow-up-right text-muted"></i></a>',
                    e($customer->name),
                    route('admin.users.show', $customer->id)
                );
            })
            ->editColumn('rating', function (ProductRating $rating) {
                $value = number_format((float) $rating->rating, 1);
                $maxStars = 5;
                $filled = (int) round((float) $rating->rating);

                $stars = collect(range(1, $maxStars))->map(function (int $position) use ($filled) {
                    $icon = $position <= $filled ? 'bi-star-fill' : 'bi-star';

                    return sprintf('<i class="bi %s text-warning"></i>', $icon);
                })->implode('');

                return sprintf(
                    '<span class="badge badge-primary mr-2">%s</span><span class="rating-stars">%s</span>',
                    $value,
                    $stars
                );
            })
            ->editColumn('comment', fn (ProductRating $rating) => e(Str::limit((string) $rating->comment, 120)))
            ->editColumn('is_anonymous', fn (ProductRating $rating) => $rating->is_anonymous ? 'YES' : 'NO')
            ->addColumn('is_publish', function (ProductRating $rating) use ($product) {
                return view('admin.products.ratings.partials.action-published', [
                    'rating' => $rating,
                    'product' => $product,
                ]);
            })
            ->addColumn('action', function (ProductRating $rating) use ($product) {
                return view('admin.products.ratings.partials.action', [
                    'rating' => $rating,
                    'product' => $product,
                ]);
            })
            ->setRowId('id')
            ->rawColumns(['order_reference', 'customer', 'rating', 'is_publish', 'action', 'comment']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductRating $model): QueryBuilder
    {
        $product = $this->getProduct();

        return $model->newQuery()
            ->with(['orderItem.order.user'])
            ->whereHas('orderItem', fn ($query) => $query->where('product_id', $product->id))
            ->select('product_ratings.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-product-ratings')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
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
                ->width(50),

            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::computed('customer', 'orderItem.order.user.name')
                ->title('CUSTOMER')
                ->orderable(true)
                ->searchable(true),

            Column::make('rating')
                ->title('RATING')
                ->orderable(true)
                ->searchable(false),

            Column::make('comment')
                ->title('COMMENT')
                ->orderable(false)
                ->searchable(true),

            Column::make('is_anonymous')
                ->title('ANONYMOUS')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::computed('is_publish')
                ->title('PUBLISHED')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
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
        return 'ProductRatings_'.date('dmY');
    }

    private function getProduct(): Product
    {
        if ($this->product instanceof Product) {
            return $this->product;
        }

        $routeProduct = request()->route('product');

        if ($routeProduct instanceof Product) {
            return $this->product = $routeProduct;
        }

        if (is_numeric($routeProduct)) {
            return $this->product = Product::findOrFail((int) $routeProduct);
        }

        throw new \RuntimeException('Product instance is required for ProductRatingsDataTable.');
    }
}
