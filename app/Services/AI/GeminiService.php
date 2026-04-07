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
        $this->model  = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Kirim prompt teks biasa, kembalikan string response.
     */
    public function ask(string $prompt, int $maxTokens = 512): string
    {
        $response = Http::timeout(30)
            ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature'     => 0.3,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('AI service tidak tersedia. Coba lagi nanti.');
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    /**
     * Kirim prompt, parse response sebagai JSON.
     * Gunakan untuk output terstruktur.
     */
    public function askJson(string $prompt): array
    {
        $fullPrompt = $prompt
            . "\n\nPENTING: Balas HANYA dengan JSON valid."
            . " Tanpa penjelasan, tanpa kalimat pembuka, tanpa markdown, tanpa backtick.";

        $raw = $this->ask($fullPrompt, maxTokens: 1024);

        // Bersihkan jika Gemini tetap membungkus dengan ```json ... ```
        $clean = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', trim($raw));

        try {
            return json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Gemini JSON parse failed', ['raw' => $raw, 'error' => $e->getMessage()]);
            return [];
        }
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
            Log::error('Gemini chat error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('AI service tidak tersedia. Coba lagi nanti.');
        }

        return $response->json('candidates.0.content.parts.0.text')
            ?? 'Maaf, saya tidak bisa merespons saat ini.';
    }
}