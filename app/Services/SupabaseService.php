<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class SupabaseService
{
    protected Client $client;

    protected string $url;

    protected string $key;

    protected string $bucket;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.supabase.url'), '/');
        $this->key = (string) config('services.supabase.key');
        $this->bucket = (string) config('services.supabase.bucket');

        $this->client = new Client([
            'headers' => [
                'Authorization' => "Bearer {$this->key}",
                'apiKey' => $this->key,
            ],
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    public function uploadFile(UploadedFile $file, string $path): string
    {
        $this->ensureConfigured();

        $filePath = trim($path, '/');
        [$localPath, $fileName, $temporary] = $this->prepareImage($file);
        $stream = fopen($localPath, 'r');

        try {
            $response = $this->requestWithRetry('POST', "{$this->url}/storage/v1/object/{$this->bucket}/{$filePath}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->key}",
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $stream,
                        'filename' => $fileName,
                    ],
                ],
            ]);

            $status = $response->getStatusCode();

            if ($status < 200 || $status >= 300) {
                throw new RuntimeException("Supabase upload gagal dengan status HTTP {$status}");
            }

            return $filePath;
        } catch (\Throwable $e) {
            Log::error('Supabase upload error: '.$e->getMessage(), ['path' => $filePath]);
            throw new RuntimeException('Gagal mengunggah gambar ke storage', 0, $e);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($temporary && is_file($localPath)) {
                @unlink($localPath);
            }
        }
    }

    public function getFileUrl(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        return self::publicUrl($filePath);
    }

    public static function publicUrl(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        $url = rtrim((string) config('services.supabase.url'), '/');
        $bucket = (string) config('services.supabase.bucket');

        return "{$url}/storage/v1/object/public/{$bucket}/".ltrim($filePath, '/');
    }

    public function deleteFile(?string $filePath): bool
    {
        if (! $filePath) {
            return true;
        }

        try {
            $filePath = trim($filePath, '/');

            $response = $this->requestWithRetry('DELETE', "{$this->url}/storage/v1/object/{$this->bucket}/{$filePath}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->key}",
                    'apiKey' => $this->key,
                ],
            ]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        } catch (\Throwable $e) {
            Log::error('Supabase delete error: '.$e->getMessage(), ['path' => $filePath]);

            return false;
        }
    }

    private function ensureConfigured(): void
    {
        if ($this->url === '' || $this->key === '' || $this->bucket === '') {
            throw new RuntimeException('Konfigurasi Supabase Storage belum lengkap');
        }
    }

    /**
     * Kirim request dengan satu kali retry saat koneksi gagal.
     */
    private function requestWithRetry(string $method, string $url, array $options, int $attempts = 2): ResponseInterface
    {
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->client->request($method, $url, $options);
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                if ($attempt >= $attempts) {
                    throw $e;
                }

                usleep(250000 * $attempt);

                foreach ($options['multipart'] ?? [] as $part) {
                    if (isset($part['contents']) && is_resource($part['contents'])) {
                        rewind($part['contents']);
                    }
                }
            }
        }

        throw new RuntimeException('Permintaan ke Supabase Storage gagal');
    }

    /**
     * Kompres gambar agar berada di bawah batas bucket sebelum diunggah.
     *
     * @return array{0: string, 1: string, 2: bool} [path lokal, nama file, apakah file sementara]
     */
    private function prepareImage(UploadedFile $file): array
    {
        $originalPath = $file->getRealPath();
        $originalName = $file->getClientOriginalName();
        $maxBytes = max(1024, (int) config('services.supabase.image_max_bytes'));
        $maxDimension = max(320, (int) config('services.supabase.image_max_dimension'));
        $mime = (string) $file->getMimeType();

        $formats = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (! isset($formats[$mime]) || ! function_exists('imagecreatefromstring')) {
            return [$originalPath, $originalName, false];
        }

        $size = (int) $file->getSize();
        $dimensions = @getimagesize($originalPath);
        $width = $dimensions[0] ?? 0;
        $height = $dimensions[1] ?? 0;

        if ($size <= $maxBytes && $width <= $maxDimension && $height <= $maxDimension) {
            return [$originalPath, $originalName, false];
        }

        $contents = @file_get_contents($originalPath);

        if ($contents === false) {
            return [$originalPath, $originalName, false];
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return [$originalPath, $originalName, false];
        }

        $scale = min(1, $maxDimension / max($width, $height, 1));
        $targetWidth = max(1, (int) floor($width * $scale));
        $targetHeight = max(1, (int) floor($height * $scale));

        $canvas = $this->createCanvas($targetWidth, $targetHeight, $mime);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        $extension = $formats[$mime];
        $temporaryPath = tempnam(sys_get_temp_dir(), 'invengo_img_');
        $temporaryFile = $temporaryPath.'.'.$extension;
        $qualitySteps = $formats[$mime] === 'png' ? [8, 9] : [82, 75, 65, 55, 45, 35];

        $attempts = 0;

        do {
            foreach ($qualitySteps as $quality) {
                $this->saveImage($canvas, $temporaryFile, $mime, $quality);

                if (is_file($temporaryFile) && filesize($temporaryFile) <= $maxBytes) {
                    break 2;
                }
            }

            if ($targetWidth <= 400 || $targetHeight <= 400) {
                break;
            }

            $targetWidth = max(1, (int) floor($targetWidth * 0.8));
            $targetHeight = max(1, (int) floor($targetHeight * 0.8));

            $resized = $this->createCanvas($targetWidth, $targetHeight, $mime);
            imagecopyresampled($resized, $canvas, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($canvas), imagesy($canvas));
            imagedestroy($canvas);
            $canvas = $resized;
            $attempts++;
        } while ($attempts < 4);

        imagedestroy($canvas);
        @unlink($temporaryPath);

        if (! is_file($temporaryFile)) {
            return [$originalPath, $originalName, false];
        }

        if (filesize($temporaryFile) > $maxBytes) {
            @unlink($temporaryFile);
            throw new RuntimeException('Gambar tidak dapat dikompres di bawah batas ukuran storage.');
        }

        $fileName = pathinfo($originalName, PATHINFO_FILENAME).'.'.$extension;

        return [$temporaryFile, $fileName, true];
    }

    private function createCanvas(int $width, int $height, string $mime)
    {
        $canvas = imagecreatetruecolor($width, $height);

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        }

        return $canvas;
    }

    private function saveImage($image, string $path, string $mime, int $quality): void
    {
        if ($mime === 'image/png') {
            imagepng($image, $path, $quality);

            return;
        }

        if ($mime === 'image/webp') {
            imagewebp($image, $path, $quality);

            return;
        }

        imagejpeg($image, $path, $quality);
    }
}
