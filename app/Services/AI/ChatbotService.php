<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    private const CACHE_PREFIX = 'chatbot_history_';
    private const MAX_TURNS    = 8;   // Simpan 8 pasang pesan (user + model)
    private const TTL          = 3600; // 1 jam

    public function __construct(
        private GeminiService $gemini,
        private TransactionService $txService,
    ) {}

    /**
     * Proses pesan dari user, kembalikan jawaban AI.
     */
    public function reply(User $user, string $message): string
    {
        $context  = $this->buildContext($user);
        $history  = $this->getHistory($user->id);
        $contents = [];

        // System prompt sebagai pasangan user/model pertama
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $this->buildSystemPrompt($user->name, $context)]],
        ];
        $contents[] = [
            'role'  => 'model',
            'parts' => [['text' => 'Halo! Saya Fina, asisten keuangan kamu. Ada yang bisa saya bantu?']],
        ];

        // Riwayat percakapan sebelumnya
        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'],
                'parts' => [['text' => $turn['content']]],
            ];
        }

        // Pesan user terbaru
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        $reply = $this->gemini->chat($contents, maxTokens: 512);

        // Simpan ke history
        $this->saveHistory($user->id, $message, $reply);

        return $reply;
    }

    /**
     * Reset riwayat percakapan.
     */
    public function clearHistory(string $userId): void
    {
        Cache::forget(self::CACHE_PREFIX . $userId);
    }

    public function getHistory(string $userId): array
    {
        return Cache::get(self::CACHE_PREFIX . $userId, []);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function buildSystemPrompt(string $name, string $context): string
    {
        return <<<PROMPT
Kamu adalah "Fina", asisten keuangan pribadi milik {$name}.

KONTEKS KEUANGAN TERKINI:
{$context}

ATURAN KAMU:
- Jawab dalam Bahasa Indonesia yang santai dan mudah dipahami
- Gunakan data aktual di atas saat menjawab pertanyaan keuangan
- Format angka dengan pemisah titik ribuan: Rp 1.500.000
- Respons maksimal 3 paragraf pendek atau 5 poin singkat
- Jika ditanya di luar topik keuangan, alihkan kembali dengan sopan
- Jangan tampilkan data mentah UUID atau format teknis ke user
PROMPT;
    }

    private function buildContext(User $user): string
    {
        $summary    = $this->txService->monthlySummary($user->id, now()->month, now()->year);
        $byCategory = $this->txService->expenseByCategory($user->id, now()->month, now()->year);
        $accounts   = $user->activeAccounts()->get(['name', 'type', 'balance']);

        $accountLines = $accounts->map(fn($a) =>
            "  - {$a->name} ({$a->type->label()}): Rp " . number_format($a->balance, 0, ',', '.')
        )->join("\n");

        $catLines = collect($byCategory)->take(5)->map(fn($c) =>
            "  - {$c['label']}: Rp " . number_format($c['amount'], 0, ',', '.')
        )->join("\n");

        $bulan = now()->translatedFormat('F Y');

        return <<<CTX
Bulan: {$bulan}

Rekening aktif:
{$accountLines}

Ringkasan {$bulan}:
  - Pemasukan: Rp {$this->fmt($summary['income'])}
  - Pengeluaran: Rp {$this->fmt($summary['expense'])}
  - Selisih: Rp {$this->fmt($summary['balance'])}

Top pengeluaran bulan ini:
{$catLines}
CTX;
    }

    private function saveHistory(string $userId, string $userMsg, string $aiMsg): void
    {
        $history   = $this->getHistory($userId);
        $history[] = ['role' => 'user',  'content' => $userMsg];
        $history[] = ['role' => 'model', 'content' => $aiMsg];

        // Potong agar tidak terlalu panjang
        if (count($history) > self::MAX_TURNS * 2) {
            $history = array_slice($history, -(self::MAX_TURNS * 2));
        }

        Cache::put(self::CACHE_PREFIX . $userId, $history, self::TTL);
    }

    private function fmt(float $n): string
    {
        return number_format($n, 0, ',', '.');
    }
}