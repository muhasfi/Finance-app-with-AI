<?php

namespace App\Services\AI;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class InsightService
{
    public function __construct(
        private GeminiService      $gemini,
        private TransactionService $txService,
    ) {}

    // -------------------------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------------------------

    /**
     * Buat ringkasan keuangan bulan ini dalam bahasa Indonesia yang natural.
     */
    public function monthlySummary(User $user, int $month, int $year): string
    {
        $summary    = $this->txService->monthlySummary($user->id, $month, $year);
        $byCategory = $this->txService->expenseByCategory($user->id, $month, $year);

        // Kalau tidak ada data sama sekali, kembalikan pesan default
        // tanpa perlu panggil Gemini (hemat token)
        if ($summary['income'] == 0 && $summary['expense'] == 0) {
            return "Belum ada data transaksi untuk bulan ini. Yuk mulai catat pemasukan dan pengeluaran kamu!";
        }

        $monthName = \Carbon\Carbon::createFromDate($year, $month)->translatedFormat('F Y');

        $catLines = collect($byCategory)->take(5)
            ->map(fn($c) => "- {$c['label']}: Rp " . number_format($c['amount'], 0, ',', '.'))
            ->join("\n");

        $income  = 'Rp ' . number_format($summary['income'],  0, ',', '.');
        $expense = 'Rp ' . number_format($summary['expense'], 0, ',', '.');
        $balance = 'Rp ' . number_format(abs($summary['balance']), 0, ',', '.');
        $status  = $summary['balance'] >= 0 ? "surplus {$balance}" : "defisit {$balance}";

        $prompt = <<<PROMPT
Kamu adalah asisten keuangan pribadi yang berbicara Bahasa Indonesia dengan nada santai dan supportif.

Data keuangan {$user->name} bulan {$monthName}:
- Pemasukan: {$income}
- Pengeluaran: {$expense}
- Kondisi: {$status}
- Top 5 kategori pengeluaran:
{$catLines}

Tulis ringkasan singkat (3-4 kalimat) dengan nada:
- Positif dan memotivasi jika surplus
- Jujur tapi tidak menghakimi jika defisit
- Sebutkan 1 hal konkret yang bisa diperbaiki bulan depan
- Bahasa santai, mudah dipahami siapa saja
- JANGAN mulai dengan sapaan seperti "Halo" atau "Hai"
PROMPT;

        try {
            return $this->gemini->ask($prompt, maxTokens: 350);
        } catch (\Throwable $e) {
            Log::error('monthlySummary failed', [
                'user_id' => $user->id,
                'month'   => $month,
                'year'    => $year,
                'error'   => $e->getMessage(),
            ]);

            return "Ringkasan keuangan tidak dapat dibuat saat ini. Silakan coba lagi nanti.";
        }
    }

    /**
     * Deteksi pengeluaran yang tidak biasa dibanding rata-rata 3 bulan lalu.
     */
    public function detectAnomalies(User $user): array
    {
        $threeMonthsAgo = now()->subMonths(3)->startOfMonth();

        // Ambil rata-rata 3 bulan terakhir per kategori
        $avgData = Transaction::forUser($user->id)
            ->expense()
            ->where('date', '>=', $threeMonthsAgo)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, AVG(amount_base) as avg_amount, MAX(amount_base) as max_amount, COUNT(*) as total')
            ->groupBy('category_id')
            ->with('category:id,name')
            ->limit(10)
            ->get();

        if ($avgData->isEmpty()) {
            return [];
        }

        // Ambil transaksi bulan ini
        $thisMonth = Transaction::forUser($user->id)
            ->expense()
            ->thisMonth()
            ->with('category:id,name')
            ->orderByDesc('amount_base')
            ->limit(15)
            ->get();

        // Kalau bulan ini tidak ada transaksi, tidak perlu panggil AI
        if ($thisMonth->isEmpty()) {
            return [];
        }

        $avgLines = $avgData->map(function ($r) {
            $category = $r->category?->name ?? 'Tanpa kategori';
            return "- {$category}: avg Rp " . number_format($r->avg_amount, 0, ',', '.')
                . ", maks Rp " . number_format($r->max_amount, 0, ',', '.')
                . " ({$r->total}x transaksi)";
        })->join("\n");

        $thisMonthLines = $thisMonth->map(function ($t) {
            $category = $t->category?->name ?? 'Tanpa kategori';
            return "- {$category}: Rp " . number_format($t->amount, 0, ',', '.')
                . " ({$t->date->format('d M')})";
        })->join("\n");

        $prompt = <<<PROMPT
Analisis data keuangan untuk mendeteksi pengeluaran tidak biasa.

RATA-RATA 3 BULAN TERAKHIR:
{$avgLines}

PENGELUARAN BULAN INI:
{$thisMonthLines}

Identifikasi maksimal 3 anomali pengeluaran yang signifikan (jika ada).

Balas JSON array:
[
  {
    "category": "nama kategori",
    "description": "penjelasan singkat dalam 1 kalimat bahasa Indonesia",
    "severity": "low|medium|high"
  }
]

Jika tidak ada anomali yang signifikan, balas: []
PROMPT;

        try {
            $result = $this->gemini->askJson($prompt, maxTokens: 800);

            if (!is_array($result)) {
                return [];
            }

            return collect($result)
                ->take(3)
                ->map(fn($item) => [
                    'category'    => $item['category']    ?? 'Tidak diketahui',
                    'description' => $item['description'] ?? '-',
                    'severity'    => in_array($item['severity'] ?? '', ['low', 'medium', 'high'])
                        ? $item['severity']
                        : 'low',
                ])
                ->values()
                ->toArray();

        } catch (\Throwable $e) {
            Log::error('detectAnomalies failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Saran hemat spesifik berdasarkan pola pengeluaran.
     * Kembalikan 3 saran actionable.
     */
    public function savingTips(User $user): array
    {
        $summary    = $this->txService->monthlySummary($user->id, now()->month, now()->year);
        $byCategory = $this->txService->expenseByCategory($user->id, now()->month, now()->year);

        // Tidak ada data → kembalikan tip default tanpa panggil Gemini
        if (empty($byCategory)) {
            return [[
                'category'         => null,
                'tip'              => 'Belum ada data transaksi bulan ini. Mulai catat pengeluaran untuk mendapat saran hemat yang personal!',
                'potential_saving' => '-',
            ]];
        }

        $catLines = collect($byCategory)->take(6)
            ->map(fn($c) => "- {$c['label']}: Rp " . number_format($c['amount'], 0, ',', '.'))
            ->join("\n");

        $ratio = $summary['income'] > 0
            ? round(($summary['expense'] / $summary['income']) * 100)
            : 100;

        $prompt = <<<PROMPT
Berikan tepat 3 saran hemat yang praktis untuk pengguna Indonesia.

Data pengeluaran bulan ini:
- Rasio pengeluaran/pemasukan: {$ratio}%
- Rincian per kategori:
{$catLines}

Balas JSON array dengan TEPAT 3 item:
[
  {
    "category": "nama kategori terkait (atau null jika saran umum)",
    "tip": "saran konkret 1-2 kalimat bahasa Indonesia, langsung bisa dipraktekkan",
    "potential_saving": "estimasi penghematan, contoh: '15-20%' atau 'Rp 300.000/bulan'"
  }
]

Saran harus spesifik, realistis, dan relevan dengan data di atas.
Pastikan JSON lengkap dan valid — tutup semua string, array, dan object dengan benar.
PROMPT;

        try {
            $result = $this->gemini->askJson($prompt, maxTokens: 1500);

            if (!is_array($result) || count($result) === 0) {
                return $this->defaultTips();
            }

            return collect($result)
                ->take(3)
                ->map(fn($item) => [
                    'category'         => $item['category']         ?? null,
                    'tip'              => $item['tip']              ?? '-',
                    'potential_saving' => $item['potential_saving'] ?? '-',
                ])
                ->values()
                ->toArray();

        } catch (\Throwable $e) {
            Log::error('savingTips failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->defaultTips();
        }
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Tips fallback jika Gemini gagal.
     */
    private function defaultTips(): array
    {
        return [
            [
                'category'         => null,
                'tip'              => 'Catat setiap pengeluaran harian agar kamu tahu ke mana uang pergi.',
                'potential_saving' => '10-20%',
            ],
            [
                'category'         => null,
                'tip'              => 'Buat anggaran bulanan dan pisahkan tabungan di awal bulan sebelum belanja.',
                'potential_saving' => 'Rp 200.000–500.000/bulan',
            ],
            [
                'category'         => null,
                'tip'              => 'Tunda pembelian non-esensial selama 24 jam sebelum memutuskan untuk beli.',
                'potential_saving' => '5-15%',
            ],
        ];
    }
}