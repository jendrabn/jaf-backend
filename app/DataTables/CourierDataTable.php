<?php

namespace App\DataTables;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CourierDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('is_active', function ($courier) {
                return '
                    <div class="custom-control custom-switch">
                        <input type="checkbox" data-url="'.route('admin.couriers.update', $courier->id).'" class="custom-control-input toggle-status" id="courier-'.$courier->id.'" data-id="'.$courier->id.'" '.($courier->is_active ? 'checked' : '').'>
                        <label class="custom-control-label font-weight-normal" for="courier-'.$courier->id.'">
                            '.($courier->is_active ? 'Active' : 'Inactive').'
                        </label>
                    </div>
               ';
            })
            ->setRowId('id')
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Courier $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('courier-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->selectStyleSingle()
            ->buttons([]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->title('ID')
                ->width(35),

            Column::make('name')
                ->title('NAME'),

            Column::computed('is_active')
                ->title('STATUS'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Courier_'.date('YmdHis');
    }
}
