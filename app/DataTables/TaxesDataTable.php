<?php

namespace App\DataTables;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TaxesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.taxes.partials.action')
            ->editColumn('rate', function (Tax $tax): string {
                $formatted = rtrim(rtrim(number_format((float) $tax->rate, 2, '.', ''), '0'), '.');

                return sprintf('<span>%s%%</span>', $formatted);
            })
            ->setRowId('id')
            ->rawColumns(['action', 'rate']);
    }

    public function query(Tax $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-taxes')
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
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Tax')
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

            Column::make('name')
                ->title('NAME')
                ->orderable(true)
                ->searchable(true),

            Column::make('rate')
                ->title('RATE')
                ->orderable(true)
                ->searchable(false),

            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false),
        ];
    }

    protected function filename(): string
    {
        return 'TAXES_' . date('dmY');
    }
}
