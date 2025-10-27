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
            ->editColumn('name', fn($role) => str()->headline($role->name))
            ->editColumn('permissions', function ($role) {
                $rolesHtml = [];

                foreach ($role->permissions as $permission) {
                    [$module, $action] = explode('.', $permission->name);

                    $action = str()->headline($action);
                    $module = str()->headline($module);

                    $rolesHtml[] = badgeLabel("{$action} {$module}", 'primary');
                }

                return implode(' ', $rolesHtml);
            })
            ->editColumn('created_at', fn($role) => optional($role->created_at)->format('d-m-Y H:i:s'))
            ->editColumn('updated_at', fn($role) => optional($role->updated_at)->format('d-m-Y H:i:s'))
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
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Role'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise mr-1"></i> Reload'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap mr-1"></i> Columns'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::make('name')
                ->title('ROLE')
                ->orderable(true)
                ->searchable(true),

            Column::make('permissions')
                ->title('PERMISSIONS')
                ->sortable(false)
                ->searchable(false),

            Column::make('users_count')
                ->title('USERS COUNT')
                ->orderable(true)
                ->searchable(false),

            Column::make('created_at')
                ->title('DATE & TIME CREATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::computed('action')
                ->exportable(false)
                ->printable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Role_' . date('YmdHis');
    }
}
