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
            ->addColumn('action', function (Campaign $campaign) {
                // Render actions via a Blade partial to keep HTML clean
                return view('admin.campaigns.partials.actions', [
                    'campaign' => $campaign,
                ])->render();
            })
            ->editColumn('status', function (Campaign $campaign) {
                $label = $campaign->status->getLabel();
                $badge = $campaign->status->getBadgeClass();

                return '<span class="badge '.e($badge).'">'.e($label).'</span>';
            })
            ->editColumn('scheduled_at', fn (Campaign $c) => $c->scheduled_at?->format('Y-m-d H:i:s'))
            ->editColumn('sent_at', fn (Campaign $c) => $c->sent_at?->format('Y-m-d H:i:s'))
            ->editColumn('created_at', fn (Campaign $c) => $c->created_at?->format('Y-m-d H:i:s'))
            ->editColumn('updated_at', fn (Campaign $c) => $c->updated_at?->format('Y-m-d H:i:s'))
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
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Campaign'),
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
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('name')->title('NAME'),
            Column::make('subject')->title('SUBJECT'),
            Column::make('status')->title('STATUS'),
            Column::make('campaign_receipts_count')->title('RECIPIENTS'),
            Column::make('scheduled_at')->title('SCHEDULED AT'),
            Column::make('sent_at')->title('SENT AT'),
            Column::make('created_at')->title('DATE & TIME CREATED')
                ->visible(false),
            Column::make('updated_at')->title('DATE & TIME UPDATED')
                ->visible(false),
            Column::computed('action')
                ->title('ACTIONS')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(160)
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
}

