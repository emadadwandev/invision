<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * List audit logs with optional filters.
     *
     * GET /api/v1/audit-logs?user_id=&action=&entity_type=&from=&to=&page=
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|integer',
            'action' => 'nullable|string',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $tenantId = $request->user()->tenant_id;

        $logs = AuditService::query($tenantId, $request->only([
            'user_id', 'action', 'entity_type', 'entity_id', 'from', 'to',
        ]))->paginate(50);

        return response()->json($logs);
    }

    /**
     * Get single audit log entry.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $log = \App\Models\AuditLog::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($id);

        return response()->json($log);
    }
}
