<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name']);
        $categories = \App\Models\Category::forUser(auth()->id())->parentsOnly()->get(['id', 'name']);

        return view('reports.index', compact('accounts', 'categories'));
    }

    /**
     * Export transaksi ke CSV.
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'type'        => ['nullable', 'in:income,expense,transfer'],
            'account_id'  => ['nullable', 'uuid'],
            'category_id' => ['nullable', 'uuid'],
        ]);

        $query = Transaction::forUser(auth()->id())
            ->with(['account:id,name', 'category:id,name'])
            ->whereBetween('date', [$request->from, $request->to])
            ->latest('date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->get();

        // Build CSV
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

        // Summary di bawah
        $income  = $transactions->where('type', 'income')->sum('amount_base');
        $expense = $transactions->where('type', 'expense')->sum('amount_base');
        $rows[]  = [];
        $rows[]  = ['', '', '', '', 'Total Pemasukan', $income];
        $rows[]  = ['', '', '', '', 'Total Pengeluaran', $expense];
        $rows[]  = ['', '', '', '', 'Selisih', $income - $expense];

        $filename = 'transaksi_' . $request->from . '_sd_' . $request->to . '.csv';

        $output = fopen('php://temp', 'r+');
        // BOM untuk Excel agar bisa baca UTF-8
        fwrite($output, "\xEF\xBB\xBF");
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
}
