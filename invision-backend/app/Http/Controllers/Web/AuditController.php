<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $logs = AuditService::query($tenantId, $request->only([
            'user_id', 'action', 'entity_type', 'from', 'to',
        ]))->paginate(50);

        return view('pages.audit.index', compact('logs'));
    }

    public function show(int $id, Request $request)
    {
        $log = \App\Models\AuditLog::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($id);

        return view('pages.audit.show', compact('log'));
    }
}
