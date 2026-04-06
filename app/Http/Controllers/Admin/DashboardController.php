<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_users'     => User::count(),
            'active_users'    => User::active()->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'new_this_month'  => User::whereMonth('created_at', now()->month)->count(),
            'total_tx'        => Transaction::count(),
            'tx_this_month'   => Transaction::whereMonth('date', now()->month)->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentLogs  = AuditLog::with('user:id,name,email')->latest('created_at')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentLogs'));
    }
}
