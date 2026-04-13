<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\BudgetResource;
use App\Http\Resources\TransactionResource;
use App\Models\Budget;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(private TransactionService $service) {}

    /**
     * Data lengkap untuk home screen Flutter.
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        // Ringkasan keuangan
        $summary = $this->service->monthlySummary($user->id, $month, $year);

        // Rekening aktif
        $accounts = $user->activeAccounts()->get();

        // 5 transaksi terbaru
        $recentTransactions = Transaction::forUser($user->id)
            ->with(['account:id,name,color,icon', 'category:id,name,color,icon'])
            ->latest('date')->latest('created_at')
            ->limit(5)
            ->get();

        // Budget bulan ini (yang kritis dulu)
        $budgets = Budget::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->active()
            ->with('category:id,name,color,icon')
            ->get()
            ->map(function ($b) {
                $b->spent_amount = $b->spent();
                $b->percentage   = $b->percentage();
                $b->status       = $b->status();
                return $b;
            })
            ->sortByDesc('percentage')
            ->take(4);

        // Chart data tren 6 bulan
        $trendData = $this->service->trendChart($user->id);

        // Chart data kategori bulan ini
        $categoryData = $this->service->expenseByCategory($user->id, $month, $year);

        return $this->success([
            'summary' => [
                'income'       => (float) $summary['income'],
                'expense'      => (float) $summary['expense'],
                'balance'      => (float) $summary['balance'],
                'total_balance'=> (float) $accounts->sum('balance'),
                'month'        => $month,
                'year'         => $year,
                'month_name'   => \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            ],
            'accounts'            => AccountResource::collection($accounts),
            'recent_transactions' => TransactionResource::collection($recentTransactions),
            'budgets'             => BudgetResource::collection($budgets),
            'trend_chart'         => $trendData,
            'category_chart'      => $categoryData,
        ]);
    }

    /**
     * Data chart saja — untuk refresh chart tanpa reload semua.
     */
    public function charts(Request $request): JsonResponse
    {
        $user  = $request->user();
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        return $this->success([
            'trend'    => $this->service->trendChart($user->id),
            'category' => $this->service->expenseByCategory($user->id, $month, $year),
        ]);
    }
}
