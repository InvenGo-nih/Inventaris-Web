<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiChatService
{
    public const OFF_TOPIC_REPLY = 'Maaf, saya hanya menjawab pertanyaan tentang website ini saja.';

    public const SYSTEM_PROMPT = <<<'PROMPT'
Kamu adalah asisten internal website Inventaris-Web (InvenGo). Jawab HANYA pertanyaan tentang website ini: inventaris/barang, kondisi Normal/Rusak, jumlah & grup inventaris, lokasi, peminjaman/pengembalian/keterlambatan, user/role/permission, dan fitur halaman website ini. Gunakan HANYA data konteks yang diberikan; jika data tidak ada, katakan tidak ada di konteks, jangan mengarang angka. Jika pertanyaan di luar topik, balas persis: Maaf, saya hanya menjawab pertanyaan tentang website ini saja. Jangan pernah menampilkan password, hash password, token, API key, cookie/session, atau data sensitif lain. Jangan mengubah/menghapus data dan jangan memberi perintah destruktif; cukup arahkan ke menu/route yang relevan. Jawab singkat dalam Bahasa Indonesia, maksimal 5 poin atau satu tabel kecil. Data peminjam ada di borrows.borrow_by (bukan users.user_id); status peminjaman Dipinjam/Dikembalikan; kondisi barang Normal/Rusak.

Aturan cakupan (paling penting):
- Topik diperbolehkan: inventaris/barang, kondisi Normal/Rusak, jumlah stok & unit, group inventaris, lokasi, peminjaman/pengembalian/keterlambatan, daftar peminjam (borrow_by), user/role/permission, QR/scan, pengecekan kondisi, dashboard, notifikasi, dan cara pakai halaman website ini.
- Topik dilarang: pertanyaan umum di luar website ini, saran bisnis umum, coding di luar repository ini, berita, hiburan, resep, olahraga, cuaca, translate, matematika bebas, atau permintaan mengubah/menghapus data.
- Untuk semua topik dilarang, jawab PERSIS dan HENTIKAN: "Maaf, saya hanya menjawab pertanyaan tentang website ini saja."
- Jangan menambahkan kalimat setelah penolakan, jangan memberi alternatif jawaban, jangan menjelaskan mengapa ditolak.
PROMPT;

    protected Client $client;

    protected string $key;

    protected string $model;

    public function __construct()
    {
        $this->key = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.5-flash');

        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'timeout' => max(1, (int) config('services.gemini.timeout', 20)),
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);
    }

    public function ask(string $systemPrompt, string $question): string
    {
        if ($this->key === '') {
            throw new RuntimeException('Kunci API Gemini belum dikonfigurasi. Tambahkan GEMINI_API_KEY di .env.');
        }

        try {
            $response = $this->client->post('models/'.rawurlencode($this->model).':generateContent', [
                'query' => ['key' => $this->key],
                'json' => [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $question],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 512,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logError($e);

            return 'Maaf, chatbot tidak dapat terhubung ke layanan AI. Periksa koneksi lalu coba lagi.';
        }

        $status = $response->getStatusCode();

        if ($status === 429) {
            Log::warning('Gemini chatbot quota exceeded', ['status' => $status]);

            return 'Maaf, kuota chatbot sedang penuh. Silakan coba lagi beberapa saat lagi.';
        }

        if ($status === 401 || $status === 403) {
            Log::error('Gemini chatbot authentication failed', ['status' => $status]);

            return 'Maaf, chatbot tidak dapat mengakses layanan AI. Silakan hubungi administrator.';
        }

        if ($status === 404) {
            Log::error('Gemini chatbot model not found', ['status' => $status, 'model' => $this->model]);

            return 'Maaf, model AI chatbot tidak tersedia saat ini. Silakan hubungi administrator.';
        }

        if ($status >= 500) {
            Log::error('Gemini chatbot service error', ['status' => $status]);

            return 'Maaf, layanan AI sedang mengalami gangguan. Silakan coba lagi nanti.';
        }

        if ($status < 200 || $status >= 300) {
            Log::error('Gemini chatbot request failed', ['status' => $status]);

            return 'Maaf, chatbot sedang tidak dapat menjawab. Silakan coba lagi nanti.';
        }

        $payload = json_decode((string) $response->getBody(), true);
        $answer = $payload['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! is_string($answer) || trim($answer) === '') {
            Log::warning('Gemini chatbot returned an empty answer', ['status' => $status]);

            return 'Maaf, chatbot belum dapat menghasilkan jawaban. Silakan coba lagi.';
        }

        return trim($answer);
    }

    private function logError(\Throwable $e): void
    {
        $message = $e->getMessage();
        $message = str_replace(
            array_filter([$this->key, rawurlencode($this->key)]),
            '[REDACTED]',
            $message
        );

        Log::error('Gemini chatbot request error', [
            'exception' => $e::class,
            'message' => $message,
        ]);
    }
}
