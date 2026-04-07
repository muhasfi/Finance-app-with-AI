<?php

namespace App\Services\AI;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class CategorizationService
{
    public function __construct(private GeminiService $gemini) {}

    /**
     * Kategorikan satu transaksi.
     * Kembalikan ['category_id' => '...', 'confidence' => 0-100]
     */
    public function categorize(Transaction $transaction): array
    {
        $transaction->loadMissing('account');
        $userId     = $transaction->account->user_id;
        $categories = $this->getCategories($userId, $transaction->type->value);

        if ($categories->isEmpty()) {
            return ['category_id' => null, 'confidence' => 0];
        }

        $list = $categories->map(fn($c) => "{$c->id}: {$c->name}")->join("\n");

        $prompt = <<<PROMPT
Kamu adalah sistem kategorisasi transaksi keuangan untuk pengguna Indonesia.

Transaksi:
- Deskripsi: "{$transaction->note}"
- Jumlah: Rp {$transaction->amount}
- Tipe: {$transaction->type->label()}

Pilih kategori PALING SESUAI dari daftar berikut (gunakan ID-nya):
{$list}

Balas dalam format JSON:
{"category_id": "<uuid>", "confidence": <angka 0-100>}

Aturan confidence:
- 90+ = sangat yakin (kata kunci jelas, misal "Indomaret" → Groceries)
- 70-89 = cukup yakin
- <70 = tidak yakin, gunakan null untuk category_id
PROMPT;

        $result = $this->gemini->askJson($prompt);

        return [
            'category_id' => $result['category_id'] ?? null,
            'confidence'  => (int) ($result['confidence'] ?? 0),
        ];
    }

    /**
     * Kategorikan banyak transaksi sekaligus — hemat API call saat import CSV.
     * Kembalikan array: [index => ['category_id' => ..., 'confidence' => ...]]
     */
    public function categorizeBulk(Collection $transactions): array
    {
        if ($transactions->isEmpty()) {
            return [];
        }

        $userId     = $transactions->first()->account->user_id;
        $categories = $this->getCategories($userId);

        $catList = $categories
            ->map(fn($c) => "{$c->id}: {$c->name} ({$c->type})")
            ->join("\n");

        $txList = $transactions
            ->map(fn($tx, $i) => "{$i}. \"{$tx->note}\" — Rp {$tx->amount} ({$tx->type->label()})")
            ->join("\n");

        $prompt = <<<PROMPT
Kategorikan daftar transaksi berikut berdasarkan kategori yang tersedia.

DAFTAR TRANSAKSI:
{$txList}

KATEGORI TERSEDIA:
{$catList}

Balas JSON array:
[
  {"index": 0, "category_id": "<uuid atau null>", "confidence": <0-100>},
  {"index": 1, "category_id": "<uuid atau null>", "confidence": <0-100>}
]
PROMPT;

        $results = $this->gemini->askJson($prompt);

        $mapped = [];
        foreach ((array) $results as $item) {
            if (isset($item['index'])) {
                $mapped[$item['index']] = [
                    'category_id' => $item['category_id'] ?? null,
                    'confidence'  => (int) ($item['confidence'] ?? 0),
                ];
            }
        }

        return $mapped;
    }

    /**
     * Terapkan hasil kategorisasi ke transaksi.
     * Hanya update jika confidence >= threshold.
     */
    public function applyToTransaction(Transaction $transaction, int $threshold = 70): bool
    {
        $result = $this->categorize($transaction);

        if ($result['confidence'] < $threshold || ! $result['category_id']) {
            return false;
        }

        $transaction->update([
            'category_id'    => $result['category_id'],
            'ai_categorized' => true,
            'ai_confidence'  => $result['confidence'],
        ]);

        return true;
    }

    private function getCategories(string $userId, ?string $type = null): Collection
    {
        return Category::forUser($userId)
            ->parentsOnly()
            ->when($type, fn($q) => $q->ofType($type))
            ->orderBy('sort_order')
            ->get(['id', 'name', 'type']);
    }
}