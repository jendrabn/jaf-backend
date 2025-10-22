<?php

namespace App\DataTables;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BlogsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.blogs.partials.action')
            ->editColumn('is_publish', 'admin.blogs.partials.action-published')
            ->editColumn('featured_image', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank">
                        <img src="%s" alt="" class="theme-avatar border border-2 border-primary rounded">
                    </a>',
                    $row->featured_image?->url,
                    $row->featured_image?->preview_url
                );
            })
            ->editColumn('tags', function ($row) {
                $tags = [];

                $row->tags->each(function ($tag) use (&$tags) {
                    $tags[] = '<span class="badge badge-info rounded-0">'.$tag->name.'</span>';
                });

                return implode(' ', $tags);
            })
            ->setRowId('id')
            ->rawColumns(['is_publish', 'action', 'featured_image', 'tags']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Blog $model): QueryBuilder
    {
        $model = $model->newQuery()
            ->with(['author', 'category', 'tags', 'media'])
            ->select('blogs.*');

        $model->when(
            request()->filled('blog_category_id'),
            fn ($q) => $q->where('blog_category_id', request('blog_category_id'))
        );
        $model->when(
            request()->filled('user_id'),
            fn ($q) => $q->where('user_id', request('user_id'))
        );

        $model->when(
            request()->filled('blog_tag_id'),
            fn ($q) => $q->whereHas('tags', fn ($q) => $q->where('blog_tag_id', request('blog_tag_id')))
        );

        $model->when(
            request()->filled('is_publish'),
            fn ($q) => $q->where('is_publish', request('is_publish'))
        );

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('blog-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Blog'),
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
                Button::make('filter')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-funnel me-1"></i> Filter'),
            ])
            ->ajax([
                'data' => 'function (data) {
                        $.each($("#form-filter").serializeArray(), function (key, val) {
                           data[val.name] = val.value;
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
                ->searchable(false)
                ->orderable(false)
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::computed('featured_image')
                ->title('IMAGE'),

            Column::make('title')
                ->title('TITLE'),

            Column::make('slug')
                ->title('SLUG')
                ->visible(false),

            Column::make('author.name')
                ->title('AUTHOR'),

            Column::computed('is_publish')
                ->title('PUBLISHED'),

            Column::make('category.name')
                ->title('CATEGORY'),

            Column::make('tags')
                ->title('TAG(S)')
                ->visible(false),

            Column::make('views_count')
                ->title('VIEWS COUNT'),

            Column::make('min_read')
                ->title('MIN READ')
                ->visible(false),

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
        return 'Blog_'.date('dmY');
    }
}

