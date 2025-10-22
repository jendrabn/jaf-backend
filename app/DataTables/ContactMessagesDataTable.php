<?php

namespace App\DataTables;

use App\Models\ContactMessage;
use App\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ContactMessagesDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        $dataTable->editColumn('created_at', function (ContactMessage $row) {
            return optional($row->created_at)->format('d-m-Y H:i');
        });

        $dataTable->editColumn('message', function (ContactMessage $row) {
            return e(Str::limit(strip_tags((string) $row->message), 80));
        });

        $dataTable->addColumn('handled_by', function (ContactMessage $row) {
            return $row->handler?->name ?? '-';
        });

        $dataTable->editColumn('handled_at', function (ContactMessage $row) {
            return $row->handled_at ? $row->handled_at->format('d-m-Y H:i') : '-';
        });

        $dataTable->editColumn('updated_at', function (ContactMessage $row) {
            return optional($row->updated_at)->format('d-m-Y H:i');
        });

        $dataTable->editColumn('status', function (ContactMessage $row) {
            try {
                $status = ContactMessageStatus::from((string) $row->status);
                $label = $status->label();
                $color = $status->color();
            } catch (\ValueError $e) {
                $label = ucfirst((string) $row->status);
                $color = 'secondary';
            }

            return '<span class="badge badge-' . e($color) . ' badge-pill">' . e($label) . '</span>';
        });

        $dataTable->addColumn('actions', function (ContactMessage $row) {
            $url = route('admin.messages.show', $row->id);

            return '<a href="' . e($url) . '" class="btn btn-sm btn-outline-primary">Show</a>';
        });

        $dataTable->rawColumns(['status', 'actions']);

        return $dataTable;
    }

    public function query(ContactMessage $model): Builder
    {
        $q = $model->newQuery()->with('handler:id,name');

        $request = request();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date('date_to'));
        }

        return $q->orderByDesc('created_at');
    }

    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->setTableId('contact-messages-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('admin.messages.index'))
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->dom('lBfrtip<"actions">')
            ->orderBy(1, 'desc')
            ->buttons(
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
                Button::make('filter')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-filter me-1"></i> Filter'),
                Button::make('colvis')
                    ->className('btn btn-default')
                    ->text('<i class="bi bi-columns-gap me-1"></i> Columns'),
                Button::make('bulkDelete')
                    ->className('btn btn-danger')
                    ->text('<i class="bi bi-trash3 me-1"></i> Delete Selected'),    //
            );
    }

    protected function getColumns(): array
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

            Column::make('phone')
                ->title('PHONE'),

            Column::make('message')
                ->title('MESSAGE'),

            Column::make('status')
                ->title('STATUS'),

            Column::make('handled_by', 'handler.name')
                ->title('HANDLED BY')
                ->orderable(false)
                ->searchable(false),

            Column::make('handled_at')
                ->title('HANDLED AT'),

            Column::make('created_at')
                ->title('DATE & TIME CREATED'),

            Column::make('updated_at')
                ->title('DATE & TIME UPDATED'),

            Column::computed('actions')
                ->title('ACTIONS')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'contact_messages_' . date('YmdHis');
    }
}
