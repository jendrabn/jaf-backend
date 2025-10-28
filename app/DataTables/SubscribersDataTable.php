<?php

namespace App\DataTables;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SubscribersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.subscribers.partials.action')
            ->editColumn('status', function ($row) {
                $status = SubscriberStatus::from($row->status);

                return badgeLabel($status->label(), $status->color());
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Subscriber $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('subscribers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
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
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Subscriber')
                    ->action('function (e, dt, node, config) { return false; }'),
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
                ->orderable(false)
                ->searchable(false),

            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::make('name')
                ->title('NAME')
                ->searchable(true)
                ->orderable(true),

            Column::make('email')
                ->title('EMAIL')
                ->searchable(true)
                ->orderable(true),

            Column::make('status')
                ->title('STATUS')
                ->searchable(true)
                ->orderable(true),

            Column::make('subscribed_at')
                ->title('SUBSCRIBED AT')
                ->visible(false)
                ->searchable(false)
                ->orderable(true),

            Column::make('unsubscribed_at')
                ->title('UNSUBSCRIBED AT')
                ->visible(false)
                ->searchable(false)
                ->orderable(true),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false)
                ->searchable(false)
                ->orderable(true),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false)
                ->searchable(false)
                ->orderable(true),

            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Subscribers_' . date('dmY');
    }
}
