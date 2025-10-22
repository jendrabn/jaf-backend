<?php

namespace App\DataTables;

use App\Models\Ewallet;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EwalletsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.ewallets.partials.action')
            ->editColumn('logo', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank">
                        <img src="%s" alt="" class="theme-avatar border border-2 border-primary rounded">
                    </a>',
                    $row->logo?->url,
                    $row->logo?->preview_url
                );
            })
            ->setRowId('id')
            ->rawColumns(['action', 'logo']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Ewallet $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-ewallets')
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
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Ewallet'),
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Deselect All'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('CSV'),
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
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::computed('logo')
                ->title('LOGO')
                ->exportable(false)
                ->printable(false),

            Column::make('name')
                ->title('NAME'),

            Column::make('account_name')
                ->title('ACCOUNT NAME'),

            Column::make('account_username')
                ->title('ACCOUNT USERNAME'),

            Column::make('phone')
                ->title('PHONE NUMBER'),

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
        return 'Ewallets_'.date('dmY');
    }
}
