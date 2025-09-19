<?php

namespace App\DataTables;

use App\Models\Order;
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
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.users.partials.action')
            ->editColumn('roles', function ($row) {
                $roles = [];

                $row->roles->each(function ($role) use (&$roles) {
                    $roles[] = strtoupper($role->name);
                });

                return implode(' ', $roles);
            })
            ->editColumn('email', function ($row) {
                return $row->email . '<a class="ml-1 icon-btn text-muted small" href="mailto:' . $row->email . '"><i class="bi bi-box-arrow-up-right"></i></a>';
            })
            ->editColumn('phone', function ($row) {
                return $row->phone . '<a class="ml-1 icon-btn text-muted small" href="https://wa.me/' . $row->phone . '"><i class="bi bi-box-arrow-up-right"></i></a>';
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
                'orders' => fn($q) => $q->where('status', Order::STATUS_COMPLETED)
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
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle me-1"></i> Create User'),
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Deselect All'),
                Button::make('excel')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-file-earmark-excel me-1"></i> Excel'),
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
                ->exportable(false)
                ->printable(false)
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::make('name')
                ->title('NAME'),

            Column::make('email')
                ->title('EMAIL'),

            Column::make('email_verified_at')
                ->title('DATE & TIME VERIFIED')
                ->visible(false),

            Column::make('roles', 'roles.name')
                ->title('ROLES')
                ->orderable(false),

            Column::make('phone')
                ->title('PHONE NUMBER'),

            Column::make('sex_label', 'sex')
                ->title('SEX')
                ->visible(false),

            Column::make('birth_date')
                ->title('BIRTH DATE')
                ->visible(false),

            Column::make('orders_count')
                ->title('ORDERS COUNT')
                ->searchable(false),

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
        return 'Users_' . date('dmY');
    }
}
