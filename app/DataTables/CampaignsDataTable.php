<?php

namespace App\DataTables;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CampaignsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Campaign>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.campaigns.partials.actions')
            ->editColumn('status', fn (Campaign $c) => badgeLabel($c->status->label(), $c->status->color()))
            ->setRowId('id')
            ->rawColumns(['status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Campaign>
     */
    public function query(Campaign $model): QueryBuilder
    {
        return $model->newQuery()->withCount('campaignReceipts');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('campaigns-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Campaign'),
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
                    ->text('<i class="bi bi-trash3 mr-1"></i> Delete Selected')
                    ->attr(['id' => 'bulkDeleteBtn']),
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
                ->addClass('text-center'),
            Column::make('id')->title('ID')
                ->orderable(true)
                ->searchable(false),
            Column::make('name')->title('NAME')
                ->orderable(true)
                ->searchable(true),
            Column::make('subject')->title('SUBJECT')
                ->orderable(true)
                ->searchable(true),
            Column::make('status')->title('STATUS')
                ->orderable(true)
                ->searchable(true),
            Column::make('campaign_receipts_count')->title('RECIPIENTS')
                ->orderable(true)
                ->searchable(false),
            Column::make('scheduled_at')->title('SCHEDULED AT')
                ->orderable(true)
                ->searchable(false),
            Column::make('sent_at')->title('SENT AT')
                ->orderable(true)
                ->searchable(false),
            Column::make('created_at')->title('DATE & TIME CREATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),
            Column::make('updated_at')->title('DATE & TIME UPDATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),
            Column::computed('action')
                ->title('ACTION')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Campaigns_'.date('YmdHis');
    }

    /**
     * Add JavaScript for bulk delete functionality.
     */
    protected function getBuilderParameters(): array
    {
        return [
            'initComplete' => 'function(settings, json) {
                $("#bulkDeleteBtn").on("click", function() {
                    var ids = $.map(settings.rows({selected: true}).data(), function(row) {
                        return row.id;
                    });

                    if (ids.length === 0) {
                        alert("No campaigns selected");
                        return;
                    }

                    if (confirm("Are you sure you want to delete the selected campaigns?")) {
                        $.ajax({
                            url: "'.route('admin.campaigns.massDestroy').'",
                            type: "DELETE",
                            data: {ids: ids},
                            success: function() {
                                settings.ajax.reload();
                            }
                        });
                    }
                });
            }',
        ];
    }
}
