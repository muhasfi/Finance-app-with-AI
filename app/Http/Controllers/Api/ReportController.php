<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private TransactionService $service) {}

    /**
     * Ringkasan per bulan untuk grafik Flutter.
     */
    public function monthly(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year'  => ['required', 'integer', 'min:2020'],
        ]);

        $user    = $request->user();
        $month   = (int) $request->month;
        $year    = (int) $request->year;
        $summary = $this->service->monthlySummary($user->id, $month, $year);

        return $this->success([
            'month'       => $month,
            'year'        => $year,
            'month_name'  => \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'income'      => (float) $summary['income'],
            'expense'     => (float) $summary['expense'],
            'balance'     => (float) $summary['balance'],
            'by_category' => $this->service->expenseByCategory($user->id, $month, $year),
        ]);
    }

    /**
     * Tren 6 bulan terakhir.
     */
    public function trend(Request $request): JsonResponse
    {
        $trend = $this->service->trendChart($request->user()->id);

        return $this->success($trend);
    }

    /**
     * Laporan per rentang tanggal dengan breakdown kategori.
     */
    public function range(Request $request): JsonResponse
    {
        $request->validate([
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'type'        => ['nullable', 'in:income,expense'],
            'account_id'  => ['nullable', 'uuid'],
            'category_id' => ['nullable', 'uuid'],
        ]);

        $transactions = Transaction::forUser($request->user()->id)
            ->with(['account:id,name', 'category:id,name,color'])
            ->whereBetween('date', [$request->from, $request->to])
            ->when($request->filled('type'),        fn($q) => $q->where('type', $request->type))
            ->when($request->filled('account_id'),  fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->latest('date')
            ->get();

        $income  = $transactions->where('type', 'income')->sum('amount_base');
        $expense = $transactions->where('type', 'expense')->sum('amount_base');

        $byCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn($g) => [
                'id'         => $g->first()->category?->id,
                'name'       => $g->first()->category?->name ?? 'Tanpa Kategori',
                'color'      => $g->first()->category?->color ?? '#6b7280',
                'total'      => (float) $g->sum('amount_base'),
                'count'      => $g->count(),
                'percentage' => $expense > 0
                    ? round(($g->sum('amount_base') / $expense) * 100, 1)
                    : 0,
            ])
            ->sortByDesc('total')
            ->values();

        return $this->success([
            'from'        => $request->from,
            'to'          => $request->to,
            'income'      => (float) $income,
            'expense'     => (float) $expense,
            'balance'     => (float) ($income - $expense),
            'count'       => $transactions->count(),
            'by_category' => $byCategory,
        ]);
    }

    /**
     * Perbandingan bulan ini vs bulan lalu.
     */
    public function comparison(Request $request): JsonResponse
    {
        $user      = $request->user();
        $thisMonth = $this->service->monthlySummary($user->id, now()->month, now()->year);
        $lastMonth = $this->service->monthlySummary($user->id, now()->subMonth()->month, now()->subMonth()->year);

        $incomeChange  = $lastMonth['income']  > 0
            ? round((($thisMonth['income']  - $lastMonth['income'])  / $lastMonth['income'])  * 100, 1)
            : 0;
        $expenseChange = $lastMonth['expense'] > 0
            ? round((($thisMonth['expense'] - $lastMonth['expense']) / $lastMonth['expense']) * 100, 1)
            : 0;

        return $this->success([
            'this_month'    => $thisMonth,
            'last_month'    => $lastMonth,
            'income_change_pct'  => $incomeChange,
            'expense_change_pct' => $expenseChange,
        ]);
    }

    public function filterMeta(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->success([
            'accounts'   => $user->activeAccounts()->get(['id', 'name']),
            'categories' => \App\Models\Category::forUser($user->id)->parentsOnly()->get(['id', 'name']),
        ]);
    }

    // Di App\Http\Controllers\Api\ReportController.php

    /**
     * Export CSV untuk Flutter
     */
    public function exportCsv(Request $request): \Illuminate\Http\Response
    {
        $request->validate([
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'type'        => ['nullable', 'in:income,expense,transfer'],
            'account_id'  => ['nullable', 'uuid'],
            'category_id' => ['nullable', 'uuid'],
        ]);

        $transactions = Transaction::forUser($request->user()->id)
            ->with(['account:id,name', 'category:id,name'])
            ->whereBetween('date', [$request->from, $request->to])
            ->when($request->filled('type'),        fn($q) => $q->where('type', $request->type))
            ->when($request->filled('account_id'),  fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->latest('date')
            ->get();

        $rows   = [];
        $rows[] = ['Tanggal', 'Tipe', 'Rekening', 'Kategori', 'Keterangan', 'Jumlah (IDR)'];

        foreach ($transactions as $tx) {
            $rows[] = [
                $tx->date->format('d/m/Y'),
                $tx->type->label(),
                $tx->account->name,
                $tx->category?->name ?? '-',
                $tx->note ?? '-',
                $tx->type->value === 'expense' ? '-' . $tx->amount : $tx->amount,
            ];
        }

        $income  = $transactions->where('type', 'income')->sum('amount_base');
        $expense = $transactions->where('type', 'expense')->sum('amount_base');
        $rows[]  = [];
        $rows[]  = ['', '', '', '', 'Total Pemasukan', $income];
        $rows[]  = ['', '', '', '', 'Total Pengeluaran', $expense];
        $rows[]  = ['', '', '', '', 'Selisih', $income - $expense];

        $filename = 'transaksi_' . $request->from . '_sd_' . $request->to . '.csv';

        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF"); // BOM UTF-8
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export PDF untuk Flutter
     */
    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'type'        => ['nullable', 'in:income,expense,transfer'],
            'account_id'  => ['nullable', 'uuid'],
            'category_id' => ['nullable', 'uuid'],
        ]);

        $transactions = Transaction::forUser($request->user()->id)
            ->with(['account:id,name', 'category:id,name,color'])
            ->whereBetween('date', [$request->from, $request->to])
            ->when($request->filled('type'),        fn($q) => $q->where('type', $request->type))
            ->when($request->filled('account_id'),  fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->latest('date')
            ->get();

        $user     = $request->user();
        $income   = $transactions->where('type', 'income')->sum('amount_base');
        $expense  = $transactions->where('type', 'expense')->sum('amount_base');
        $balance  = $income - $expense;
        $fromDate = \Carbon\Carbon::parse($request->from)->translatedFormat('d F Y');
        $toDate   = \Carbon\Carbon::parse($request->to)->translatedFormat('d F Y');

        $byCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn($g) => [
                'name'  => $g->first()->category?->name ?? 'Tanpa Kategori',
                'color' => $g->first()->category?->color ?? '#6b7280',
                'total' => $g->sum('amount_base'),
            ])
            ->sortByDesc('total')
            ->values();

        $pdf      = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', compact(
            'transactions', 'user',
            'income', 'expense', 'balance',
            'fromDate', 'toDate', 'byCategory'
        ))->setPaper('a4', 'portrait');

        $filename = 'laporan_' . $request->from . '_sd_' . $request->to . '.pdf';

        return $pdf->download($filename);
    }
}
