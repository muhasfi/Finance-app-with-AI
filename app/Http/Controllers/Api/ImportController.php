<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    use ApiResponse;

    /**
     * Upload & langsung import CSV dari Flutter.
     *
     * POST /api/import/upload
     * multipart/form-data:
     *   - file        : file CSV
     *   - account_id  : uuid rekening tujuan
     *   - bank        : string (BCA|Mandiri|BRI|...) — opsional, untuk log
     *   - date_format : string, misal "d/M/yyyy"
     *   - type_default: income|expense
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'         => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'account_id'   => [
                'required', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'bank'         => ['nullable', 'string', 'max:50'],
            'date_format'  => ['required', 'string', 'max:20'],
            'type_default' => ['required', 'in:income,expense'],
        ]);

        $account = Account::findOrFail($request->account_id);

        // Simpan file sementara
        $path = $request->file('file')->store('imports', 'local');

        $rows = $this->parseCsv(Storage::disk('local')->path($path));

        // Hapus file temp setelah dibaca
        Storage::disk('local')->delete($path);

        if (empty($rows)) {
            return $this->error('File CSV kosong atau tidak valid.', 422);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        DB::transaction(function () use ($rows, $request, $account, &$imported, &$skipped, &$errors) {
            foreach ($rows as $index => $row) {
                try {
                    // ── Cari kolom tanggal secara otomatis ──────────────────
                    $dateValue = $this->findColumnValue($row, ['tanggal', 'date', 'tgl', 'transaction date', 'trans date']);
                    if (! $dateValue) {
                        $skipped++;
                        continue;
                    }

                    // Konversi format tanggal dari Flutter ("d/M/yyyy" → PHP "j/n/Y")
                    $phpFormat = $this->toPhpDateFormat($request->date_format);
                    $date = \Carbon\Carbon::createFromFormat($phpFormat, trim($dateValue))->format('Y-m-d');

                    // ── Cari kolom jumlah ───────────────────────────────────
                    $rawAmount = $this->findColumnValue($row, ['jumlah', 'amount', 'nominal', 'debit', 'kredit', 'mutasi']);
                    $amount    = $this->parseAmount($rawAmount ?? '0');

                    if ($amount <= 0) {
                        $skipped++;
                        continue;
                    }

                    // ── Tentukan tipe transaksi ─────────────────────────────
                    $type = $request->type_default;
                    $rawType = $this->findColumnValue($row, ['type', 'tipe', 'jenis', 'ket', 'keterangan transaksi', 'cr/db']);
                    if ($rawType) {
                        $lower = strtolower(trim($rawType));
                        if (str_contains($lower, 'kredit') || str_contains($lower, 'cr') || str_contains($lower, 'masuk')) {
                            $type = 'income';
                        } elseif (str_contains($lower, 'debit') || str_contains($lower, 'db') || str_contains($lower, 'keluar')) {
                            $type = 'expense';
                        }
                    }

                    // ── Deskripsi / catatan ─────────────────────────────────
                    $note = $this->findColumnValue($row, ['keterangan', 'description', 'deskripsi', 'uraian', 'remark', 'note']);

                    // ── Deteksi duplikat via hash ───────────────────────────
                    $hash = md5($account->id . $date . $amount . $type . $note);
                    if (Transaction::where('import_hash', $hash)->exists()) {
                        $skipped++;
                        continue;
                    }

                    Transaction::create([
                        'account_id'  => $account->id,
                        'type'        => $type,
                        'amount'      => $amount,
                        'amount_base' => $amount,
                        'currency'    => $account->currency,
                        'date'        => $date,
                        'note'        => $note,
                        'import_hash' => $hash,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }
        });

        return $this->success([
            'imported' => $imported,
            'skipped'  => $skipped,
            'total'    => count($rows),
            'errors'   => array_slice($errors, 0, 10), // max 10 error detail
        ], "Import selesai: {$imported} transaksi berhasil, {$skipped} dilewati.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Parse CSV → array of associative rows.
     */
    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if (! $handle) return [];

        $headers = null;
        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            // Coba juga dengan semicolon jika koma tidak menghasilkan kolom
            if ($headers === null && count($line) === 1) {
                rewind($handle);
                while (($line = fgetcsv($handle, 0, ';')) !== false) {
                    if ($headers === null) {
                        $line[0] = ltrim($line[0], "\xEF\xBB\xBF");
                        $headers = array_map('trim', $line);
                        continue;
                    }
                    if (count($line) === count($headers)) {
                        $rows[] = array_combine($headers, array_map('trim', $line));
                    }
                }
                fclose($handle);
                return $rows;
            }

            if ($headers === null) {
                $line[0] = ltrim($line[0], "\xEF\xBB\xBF");
                $headers = array_map('trim', $line);
                continue;
            }
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, array_map('trim', $line));
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Cari nilai kolom berdasarkan daftar kemungkinan nama header (case-insensitive).
     */
    private function findColumnValue(array $row, array $possibleKeys): ?string
    {
        foreach ($row as $key => $value) {
            if (in_array(strtolower(trim($key)), $possibleKeys)) {
                $val = trim($value);
                return $val !== '' ? $val : null;
            }
        }
        // Fallback: return nilai kolom pertama yang tidak kosong jika tidak ada match
        return null;
    }

    /**
     * Bersihkan dan parse string angka ke float.
     * Mendukung format: 1.000.000,00 | 1,000,000.00 | 1000000
     */
    private function parseAmount(string $raw): float
    {
        // Hapus karakter selain angka, titik, koma, dan minus
        $cleaned = preg_replace('/[^0-9,.\-]/', '', $raw);

        // Format Indonesia: 1.000.000,50 → 1000000.50
        if (preg_match('/\d{1,3}(\.\d{3})+(,\d+)?$/', $cleaned)) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        }
        // Format dengan koma ribuan: 1,000,000.50
        elseif (preg_match('/\d{1,3}(,\d{3})+(\.\d+)?$/', $cleaned)) {
            $cleaned = str_replace(',', '', $cleaned);
        }
        // Jika hanya ada koma (tanpa titik): 1000000,50 → 1000000.50
        elseif (substr_count($cleaned, ',') === 1 && substr_count($cleaned, '.') === 0) {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        return abs((float) $cleaned);
    }

    /**
     * Konversi format tanggal dari Flutter/Java style ke PHP style.
     * Contoh: "d/M/yyyy" → "j/n/Y"
     *         "dd/MM/yyyy" → "d/m/Y"
     *         "yyyy-MM-dd" → "Y-m-d"
     */
    private function toPhpDateFormat(string $format): string
    {
        return str_replace(
            ['yyyy', 'yy', 'MM', 'M',  'dd', 'd'],
            ['Y',    'y',  'm',  'n',  'd',  'j'],
            $format
        );
    }
}