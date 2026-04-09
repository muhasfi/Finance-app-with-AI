<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    private string $model;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Kirim prompt teks biasa, kembalikan string response.
     */
    public function ask(string $prompt, int $maxTokens = 512): string
    {
        $response = $this->sendRequest($prompt, $maxTokens, temperature: 0.3);

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    /**
     * Kirim prompt, parse response sebagai JSON.
     * Gunakan untuk output terstruktur.
     */
    public function askJson(string $prompt, int $maxTokens = 1500): mixed
    {
        // Paksa Gemini balas JSON murni
        $fullPrompt = $prompt
            . "\n\nPENTING: Balas HANYA dengan JSON valid dan lengkap."
            . " Pastikan semua string ditutup, semua array/object ditutup dengan benar."
            . " Tanpa penjelasan, tanpa kalimat pembuka, tanpa markdown, tanpa backtick.";

        $response = $this->sendRequest($fullPrompt, $maxTokens, temperature: 0.1);
        $raw      = $response->json('candidates.0.content.parts.0.text') ?? '';

        return $this->parseJson($raw);
    }

    /**
     * Kirim percakapan multi-turn (untuk chatbot).
     */
    public function chat(array $contents, int $maxTokens = 512): string
    {
        $response = Http::timeout(30)
            ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents'         => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature'     => 0.7,
                ],
            ]);

        if ($response->failed()) {
            $this->throwApiException($response);
        }

        return $response->json('candidates.0.content.parts.0.text')
            ?? 'Maaf, saya tidak bisa merespons saat ini.';
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Kirim request ke Gemini API (shared logic untuk ask & askJson).
     */
    private function sendRequest(string $prompt, int $maxTokens, float $temperature): \Illuminate\Http\Client\Response
    {
        $response = Http::timeout(30)
            ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature'     => $temperature,
                ],
            ]);

        if ($response->failed()) {
            $this->throwApiException($response);
        }

        return $response;
    }

    /**
     * Parse JSON dari response Gemini.
     * Handles: markdown fences, truncated JSON, trailing commas.
     */
    private function parseJson(string $raw): mixed
    {
        if (empty($raw)) {
            return [];
        }

        // Step 1: Hapus markdown code block ```json ... ```
        $cleaned = preg_replace('/^```json\s*/i', '', trim($raw));
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);
        $cleaned = trim($cleaned);

        // Step 2: Coba parse langsung
        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Step 3: Coba repair JSON yang terpotong
        $repaired = $this->repairJson($cleaned);
        $decoded  = json_decode($repaired, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            Log::warning('Gemini JSON repaired (truncated response)', [
                'original_length' => strlen($raw),
                'repaired_length' => strlen($repaired),
            ]);
            return $decoded;
        }

        // Step 4: Gagal total
        Log::warning('Gemini JSON parse failed', [
            'raw'   => substr($raw, 0, 500),
            'error' => json_last_error_msg(),
        ]);

        return [];
    }

    /**
     * Perbaiki JSON yang terpotong di tengah akibat token limit.
     *
     * Contoh kasus:
     * [{"category":"Transport","tip":"Kurangi bensin   <- putus di sini
     *
     * Hasilnya:
     * [{"category":"Transport","tip":"Kurangi bensin"}]
     */
    private function repairJson(string $json): string
    {
        // Hapus trailing koma sebelum ] atau }
        $json = preg_replace('/,\s*([\]\}])/', '$1', $json);

        $trimmed  = rtrim($json);
        $lastChar = substr($trimmed, -1);

        // Kalau string belum ditutup, tutup dulu
        if ($lastChar !== '"' && $lastChar !== '}' && $lastChar !== ']') {
            // Hapus kata terakhir yang mungkin terpotong di tengah
            $json = preg_replace('/,?\s*"[^"]*$/', '', $trimmed);
        } elseif ($lastChar === ',') {
            $json = rtrim($trimmed, ',');
        } else {
            $json = $trimmed;
        }

        // Tutup semua { dan [ yang belum ditutup
        $openBraces   = substr_count($json, '{') - substr_count($json, '}');
        $openBrackets = substr_count($json, '[') - substr_count($json, ']');

        $json .= str_repeat('}', max(0, $openBraces));
        $json .= str_repeat(']', max(0, $openBrackets));

        return $json;
    }

    /**
     * Lempar exception yang tepat berdasarkan HTTP status Gemini.
     */
    private function throwApiException(\Illuminate\Http\Client\Response $response): never
    {
        $status = $response->status();

        Log::error('Gemini API error', [
            'status' => $status,
            'body'   => $response->body(),
        ]);

        throw match (true) {
            $status === 503 => new \RuntimeException('Gemini 503: service unavailable', 503),
            $status === 429 => new \RuntimeException('Gemini 429: quota exceeded', 429),
            $status === 401 => new \RuntimeException('Gemini 401: invalid API key', 401),
            default         => new \RuntimeException("Gemini {$status}: AI service error", $status),
        };
    }
}