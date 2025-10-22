<?php

namespace App\DataTables;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AuditLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            // === Kolom tampilan (computed / edit) ===
            ->addColumn('event_badge', function (AuditLog $row) {
                $map = [
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    'bulk_deleted' => 'danger',
                    'restored' => 'info',
                ];
                $color = $map[$row->event] ?? 'secondary';
                $text = strtoupper($row->event ?? 'unknown');

                return "<span class='badge bg-{$color}'>{$text}</span>";
            })

            ->addColumn('subject_display', function (AuditLog $row) {
                $type = class_basename($row->subject_type ?? '');
                if (! $type && $row->subject) {
                    $type = class_basename($row->subject::class);
                }
                $id = $row->subject_id ?? '—';

                return "<code>{$type}#{$id}</code>";
            })

            ->addColumn('actor', function (AuditLog $row) {
                if ($row->user) {
                    $name = e($row->user->name ?? 'User');
                    $email = e($row->user->email ?? '');
                    $url = e(route('admin.users.show', $row->user->id));

                    return
                        "<div>{$name}
                            <a class=\"ms-1 text-muted small\" href=\"{$url}\" target=\"_blank\" rel=\"noopener\" title=\"Open user\">
                                <i class=\"bi bi-box-arrow-up-right\"></i>
                            </a>
                            <div class=\"text-muted small\">{$email}</div>
                        </div>";
                }

                return "<span class='text-muted'>System</span>";
            })

            ->addColumn('request', function (AuditLog $row) {
                $method = e($row->method ?? '—');
                $ip = e($row->ip ?? ($row->host ?? '—'));
                $url = e(str($row->url ?? '')->limit(48));

                return "<div><strong>{$method}</strong> · {$ip}<div class='text-muted small'>{$url}</div></div>";
            })

            ->addColumn('changed_count', function (AuditLog $row) {
                $count = is_array($row->changed) ? count($row->changed) : (collect($row->changed)->count());

                return "<span class='badge bg-light'>{$count}</span>";
            })

            ->editColumn('created_at', function (AuditLog $row) {
                return optional($row->created_at)->format('d-m-Y H:i:s');
            })

            // Action tombol (lihat/hapus dkk)
            ->addColumn('action', 'admin.auditlogs.partials.action')

            // === Search & order tambahan ===
            ->filterColumn('subject_display', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('subject_type', 'like', "%{$keyword}%")
                        ->orWhere('subject_id', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('event_badge', 'event $1')
            ->orderColumn('actor', 'user_id $1')
            ->orderColumn('request', 'method $1')

            // === Row styling ===
            ->setRowId('id')
            ->setRowClass(function (AuditLog $row) {
                return match ($row->event) {
                    'deleted' => 'table-danger',
                    'bulk_deleted' => 'table-danger',
                    'updated' => 'table-warning',
                    'created' => 'table-success',
                    'restored' => 'table-info',
                    default => '',
                };
            })

            // html kolom yang berisi markup
            ->rawColumns(['event_badge', 'subject_display', 'actor', 'request', 'changed_count', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AuditLog $model): QueryBuilder
    {
        // eager load user untuk menghindari N+1
        return $model->newQuery()
            ->with(['user:id,name,email'])
            ->select('audit_logs.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('auditlog-datatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc') // default: created_at desc (lihat indeks kolom di getColumns)
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
                Button::make('selectAll')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-check2-all me-1"></i> Select All'),
                Button::make('selectNone')
                    ->className('btn btn-primary')
                    ->text('<i class="bi bi-slash-circle me-1"></i> Deselect All'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap me-1"></i> Columns'),
                Button::make('copy')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-clipboard-check me-1"></i> Copy'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-filetype-csv me-1"></i> CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise me-1"></i> Reload'),
                Button::make('csv')
                    ->className('btn btn-default')
                    ->text('CSV'),
                Button::make('reload')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-arrow-clockwise me-1"></i> Reload'),
                // removed built-in print button per project conventions
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
                ->width(10),

            Column::make('id')->title('ID')->width(60),

            Column::computed('event_badge')
                ->title('EVENT')
                ->exportable(false)
                ->printable(false),

            Column::computed('subject_display')
                ->title('SUBJECT')
                ->addClass('text-nowrap')
                ->searchable(true)
                ->exportable(false)
                ->printable(false),

            Column::computed('actor')
                ->title('ACTOR')
                ->addClass('text-nowrap')
                ->exportable(false)
                ->printable(false),

            Column::computed('changed_count')
                ->title('FIELDS CHANGED')
                ->exportable(false)
                ->printable(false),

            Column::computed('request')
                ->title('REQUEST')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-nowrap'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED'),

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
        return 'AuditLog_'.date('YmdHis');
    }
}

