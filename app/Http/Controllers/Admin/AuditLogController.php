<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action,  fn($q) => $q->where('action', $request->action))
            ->when($request->from,    fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,      fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'actions'));
    }
}
