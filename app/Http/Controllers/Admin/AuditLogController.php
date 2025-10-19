<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AuditLogDataTable;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Index view data for audit log based on AuditLogDataTable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AuditLogDataTable $dataTable)
    {
        return $dataTable->render('admin.auditLogs.index');
    }

    /**
     * Show the form for editing the given audit log.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(AuditLog $auditLog)
    {
        return view('admin.auditLogs.show', compact('auditLog'));
    }

    /**
     * Delete the given audit log.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete();

        return response()->json(['message' => 'Audit log deleted successfully.']);
    }

    /**
     * Mass delete audit logs based on provided IDs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(Request $request)
    {
        $ids = $request->ids;
        $count = count($ids);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:audit_logs,id'],
        ]);

        AuditLog::whereIn('id', $request->ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_audit_logs',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' audit logs'],
            ],
            subjectId: null,
            subjectType: \App\Models\AuditLog::class
        );

        return response()->json(['message' => 'Audit logs deleted successfully.']);
    }
}
