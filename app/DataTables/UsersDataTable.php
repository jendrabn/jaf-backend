<?php

namespace App\DataTables;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.users.partials.action')
            ->editColumn('roles', function ($row) {
                $roles = [];

                $row->roles->each(function ($role) use (&$roles) {
                    $roles[] = badgeLabel(strtoupper($role->name), 'light');
                });

                return implode(' ', $roles);
            })
            ->editColumn('email', function ($row) {
                return $row->email . externalIconLink('mailto:' . $row->email);
            })
            ->editColumn('phone', function ($row) {
                if (! $row->phone) {
                    return '-';
                }

                return e($row->phone) . externalIconLink('tel:' . e($row->phone));
            })
            ->setRowId('id')
            ->rawColumns(['action', 'roles', 'email', 'phone']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['roles'])
            ->select('users.*')
            ->withCount([
                'orders' => fn($q) => $q->where('status', OrderStatus::Completed->value),
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-users')
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
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create User'),
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
                ->printable(false),

            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::make('name')
                ->title('NAME')
                ->orderable(true)
                ->searchable(true),

            Column::make('email')
                ->title('EMAIL')
                ->orderable(true)
                ->searchable(true),

            Column::make('email_verified_at')
                ->title('DATE & TIME VERIFIED')
                ->visible(false)
                ->orderable(true)
                ->searchable(false),

            Column::make('roles', 'roles.name')
                ->title('ROLES')
                ->orderable(false)
                ->searchable(false),

            Column::make('phone')
                ->title('PHONE NUMBER')
                ->orderable(true)
                ->searchable(true),

            Column::make('sex_label', 'sex')
                ->title('SEX')
                ->visible(false)
                ->orderable(false)
                ->searchable(false),

            Column::make('birth_date')
                ->title('BIRTH DATE')
                ->visible(false)
                ->orderable(true)
                ->searchable(true),

            Column::make('orders_count')
                ->title('ORDERS COUNT')
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
        return 'Users_' . date('dmY');
    }
}
