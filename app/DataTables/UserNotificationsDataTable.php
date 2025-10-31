<?php

namespace App\DataTables;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserNotificationsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.user-notifications.partials.action')
            ->editColumn('user.name', function ($row) {
                return $row->user ? $row->user->name : 'All Users';
            })
            ->editColumn('category', function ($row) {
                $category = NotificationCategory::tryFrom($row->category);
                if (! $category) {
                    return $row->category;
                }

                return badgeLabel($category->label(), $category->color());
            })
            ->editColumn('level', function ($row) {
                $level = NotificationLevel::tryFrom($row->level);
                if (! $level) {
                    return $row->level;
                }

                return badgeLabel($level->label(), $level->color());
            })
            ->editColumn('title', function ($row) {
                return '<strong>'.e($row->title).'</strong>';
            })
            ->editColumn('body', function ($row) {
                return Str::limit(e($row->body), 100);
            })
            ->editColumn('url', function ($row) {
                if (! $row->url) {
                    return '-';
                }

                return '<a href="'.e($row->url).'" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> Link
                </a>';
            })
            ->editColumn('read_at', function ($row) {
                if ($row->read_at) {
                    return badgeLabel('Read', 'success').'<br><small>'.$row->read_at->format('d M Y H:i').'</small>';
                }

                return badgeLabel('Unread', 'warning');
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M Y H:i');
            })
            ->setRowId('id')
            ->rawColumns(['action', 'category', 'level', 'title', 'body', 'url', 'read_at']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserNotification $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['user'])
            ->select('user_notifications.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-user-notifications')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->orderBy(1, 'desc')
            ->buttons([
                Button::make('create')
                    ->className('btn btn-success')
                    ->text('<i class="bi bi-plus-circle mr-1"></i> Create Notification'),
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
                ->width(50),

            Column::make('id')
                ->title('ID')
                ->orderable(true)
                ->searchable(false),

            Column::make('user.name')
                ->title('USER')
                ->orderable(true)
                ->searchable(true),

            Column::make('title')
                ->title('TITLE')
                ->orderable(true)
                ->searchable(true),

            Column::make('body')
                ->title('MESSAGE')
                ->orderable(false)
                ->searchable(true),

            Column::make('category')
                ->title('CATEGORY')
                ->orderable(true)
                ->searchable(true),

            Column::make('level')
                ->title('LEVEL')
                ->orderable(true)
                ->searchable(true),

            Column::make('url')
                ->title('URL')
                ->orderable(false)
                ->searchable(false),

            Column::make('read_at')
                ->title('STATUS')
                ->orderable(true)
                ->searchable(false),

            Column::make('created_at')
                ->title('CREATED AT')
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
        return 'UserNotifications_'.date('dmY');
    }
}
