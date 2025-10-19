<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RoleDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.roles.partials.action')
            ->editColumn('name', function ($role) {
                return str()->headline($role->name);
            })
            ->editColumn('permissions', function ($role) {
                $rolesHtml = [];

                foreach ($role->permissions as $permission) {
                    [$module, $action] = explode('.', $permission->name);

                    $action = str()->headline($action);
                    $module = str()->headline($module);

                    $rolesHtml[] = '<span class="badge badge-primary m-1 rounded-0 text-white" style="font-size: 0.9rem; font-weight: 500;">'.$action.' '.$module.'</span>';
                }

                return implode(' ', $rolesHtml);
            })
            ->rawColumns(['action', 'permissions'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery()->with(['permissions'])
            ->withCount('users')
            ->whereNot('name', 'admin')
            ->whereNot('name', 'user');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('role-datatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create Role'),
                Button::make('excel')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-file-earmark-excel me-1"></i> Excel'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap me-1"></i> Columns'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->title('ID'),
            Column::make('name')
                ->title('ROLE'),
            Column::make('permissions')
                ->title('PERMISSIONS'),
            Column::make('users_count')
                ->title('USERS COUNT'),
            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false),
            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Role_'.date('YmdHis');
    }
}
