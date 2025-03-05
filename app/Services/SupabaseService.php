<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;

class SupabaseService
{
    protected $client;
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL') . "/storage/v1/object";
        $this->key = env('SUPABASE_SERVICE_ROLE_KEY'); // Pakai Service Role Key untuk izin penuh
        $this->bucket = env('SUPABASE_STORAGE_BUCKET');

        $this->client = new Client([
            'headers' => [
                'Authorization' => "Bearer {$this->key}",
                'apiKey' => $this->key,
            ]
        ]);
    }

    // Upload file ke Supabase Storage
    public function uploadFile(UploadedFile $file, $path)
    {
        $filePath = "{$path}"; // Nama unik
        
        $response = $this->client->request('POST', "{$this->url}/{$this->bucket}/upload/{$filePath}", [
            'headers' => [
                'Authorization' => "Bearer {$this->key}",
                'apiKey' => $this->key,
            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->path(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            return $filePath; // Kembalikan path file yang disimpan
        }

        return null;
    }

    // Dapatkan URL file dari Supabase
    public function getFileUrl($filePath)
    {
        return env('SUPABASE_URL') . "/storage/v1/object/public/{$this->bucket}/{$filePath}";
    }

    // Hapus file dari Supabase
    public function deleteFile($filePath)
    {
        $response = $this->client->request('DELETE', "{$this->url}/{$this->bucket}/upload/{$filePath}", [
            'json' => [] // Supabase butuh body kosong dalam request DELETE
        ]);

        return $response->getStatusCode() === 200;
    }
}
