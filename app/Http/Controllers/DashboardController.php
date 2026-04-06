<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function __invoke(): View
    {
        $user  = auth()->user();
        $month = now()->month;
        $year  = now()->year;

        $summary          = $this->service->monthlySummary($user->id, $month, $year);
        $accounts         = $user->activeAccounts()->get();
        $totalBalance     = $accounts->sum('balance');
        $trendData        = $this->service->trendChart($user->id);
        $categoryData     = $this->service->expenseByCategory($user->id, $month, $year);

        $recentTransactions = Transaction::forUser($user->id)
            ->with(['account:id,name,color,icon', 'category:id,name,color,icon'])
            ->latest('date')->latest('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'summary', 'accounts', 'totalBalance',
            'trendData', 'categoryData', 'recentTransactions'
        ));
    }
}
