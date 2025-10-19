<?php

namespace App\DataTables;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
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

        $dataTable->editColumn('status', function (ContactMessage $row) {
            $map = [
                'new' => ['label' => 'New', 'color' => 'secondary'],
                'in_progress' => ['label' => 'In Progress', 'color' => 'warning'],
                'resolved' => ['label' => 'Resolved', 'color' => 'success'],
                'spam' => ['label' => 'Spam', 'color' => 'danger'],
            ];
            $conf = $map[$row->status] ?? ['label' => ucfirst($row->status), 'color' => 'secondary'];

            return '<span class="badge badge-' . e($conf['color']) . ' badge-pill">' . e($conf['label']) . '</span>';
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
            ->orderBy(0, 'desc')
            ->parameters([
                'processing' => true,
                'serverSide' => true,
                'searchDelay' => 350,
                'order' => [[0, 'desc']],
            ]);
    }

    protected function getColumns(): array
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
            ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
            ['data' => 'phone', 'name' => 'phone', 'title' => 'Phone'],
            ['data' => 'message', 'name' => 'message', 'title' => 'Message'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'handled_by', 'name' => 'handler.name', 'title' => 'Handled By', 'orderable' => false, 'searchable' => false],
            ['data' => 'handled_at', 'name' => 'handled_at', 'title' => 'Handled At'],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false],
        ];
    }

    protected function filename(): string
    {
        return 'contact_messages_' . date('YmdHis');
    }
}
