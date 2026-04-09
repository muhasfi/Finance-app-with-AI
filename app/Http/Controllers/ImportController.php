<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function create(): View
    {
        $accounts = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color']);

        return view('import.create', compact('accounts'));
    }

    /**
     * Upload CSV dan tampilkan preview kolom.
     */
    public function upload(Request $request): View|RedirectResponse
    {
        $request->validate([
            'account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'file'       => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $account = Account::findOrFail($request->account_id);
        abort_if($account->user_id !== auth()->id(), 403);

        $path = $request->file('file')->store('imports', 'local');
        $rows = $this->parseCsv(
            Storage::disk('local')->path($path)
        );

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File CSV kosong atau tidak valid.']);
        }

        $headers  = array_keys($rows[0]);
        $preview  = array_slice($rows, 0, 5);

        // Simpan data di session untuk langkah konfirmasi
        Session::put('import_data', [
            'path'       => $path,
            'account_id' => $account->id,
            'headers'    => $headers,
        ]);

        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name']);
        $categories = Category::forUser(auth()->id())->parentsOnly()->get(['id', 'name', 'type']);

        return view('import.preview', compact(
            'headers', 'preview', 'account', 'accounts', 'categories'
        ));
    }

    /**
     * Konfirmasi mapping kolom dan jalankan import.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $importData = Session::get('import_data');

        if (! $importData) {
            return redirect()->route('import.create')
                ->withErrors(['file' => 'Session expired. Silakan upload ulang.']);
        }

        $request->validate([
            'col_date'        => ['required', 'string'],
            'col_amount'      => ['required', 'string'],
            'col_description' => ['nullable', 'string'],
            'col_type'        => ['nullable', 'string'],
            'type_default'    => ['required', 'in:income,expense'],
            'date_format'     => ['required', 'string'],
        ]);

        $account  = Account::findOrFail($importData['account_id']);
        abort_if($account->user_id !== auth()->id(), 403);

        // $rows     = $this->parseCsv(storage_path('app/' . $importData['path']));
        $rows = $this->parseCsv(Storage::disk('local')->path($importData['path']));
        $imported = 0;
        $skipped  = 0;

        DB::transaction(function () use ($rows, $request, $account, &$imported, &$skipped) {
            foreach ($rows as $row) {
                try {
                    // Parse tanggal
                    $rawDate = $row[$request->col_date] ?? null;
                    if (! $rawDate) { $skipped++; continue; }

                    $date = \Carbon\Carbon::createFromFormat(
                        $request->date_format,
                        trim($rawDate)
                    )->format('Y-m-d');

                    // Parse jumlah — hapus titik ribuan, ganti koma desimal
                    $rawAmount = $row[$request->col_amount] ?? 0;
                    $amount    = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9,.]/', '', $rawAmount));

                    if ($amount <= 0) { $skipped++; continue; }

                    // Tentukan tipe
                    $type = $request->type_default;
                    if ($request->col_type && isset($row[$request->col_type])) {
                        $rawType = strtolower(trim($row[$request->col_type]));
                        if (str_contains($rawType, 'kredit') || str_contains($rawType, 'cr') || str_contains($rawType, 'masuk')) {
                            $type = 'income';
                        } elseif (str_contains($rawType, 'debit') || str_contains($rawType, 'db') || str_contains($rawType, 'keluar')) {
                            $type = 'expense';
                        }
                    }

                    $note = $request->col_description ? ($row[$request->col_description] ?? null) : null;

                    // Deteksi duplikat via hash
                    $hash = md5($account->id . $date . $amount . $type . $note);
                    if (Transaction::where('import_hash', $hash)->exists()) {
                        $skipped++;
                        continue;
                    }

                    Transaction::create([
                        'account_id'   => $account->id,
                        'type'         => $type,
                        'amount'       => $amount,
                        'amount_base'  => $amount,
                        'currency'     => $account->currency,
                        'date'         => $date,
                        'note'         => $note,
                        'import_hash'  => $hash,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }
            }
        });

        Session::forget('import_data');

        return redirect()->route('transactions.index')
            ->with('success', "Import selesai: {$imported} transaksi berhasil diimpor, {$skipped} dilewati.");
    }

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if (! $handle) return [];

        $headers = null;
        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                // Bersihkan BOM UTF-8 jika ada
                $line[0]  = ltrim($line[0], "\xEF\xBB\xBF");
                $headers  = array_map('trim', $line);
                continue;
            }
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, array_map('trim', $line));
            }
        }

        fclose($handle);
        return $rows;
    }
}
