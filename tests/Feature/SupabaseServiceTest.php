<?php

use App\Services\SupabaseService;
use Illuminate\Http\UploadedFile;

it('builds public storage urls from configuration', function () {
    config([
        'services.supabase.url' => 'https://project.supabase.co/',
        'services.supabase.bucket' => 'inventaris',
    ]);

    expect(SupabaseService::publicUrl('images/foto.jpg'))
        ->toBe('https://project.supabase.co/storage/v1/object/public/inventaris/images/foto.jpg')
        ->and(SupabaseService::publicUrl(null))->toBeNull();
});

it('compresses and resizes images before upload', function () {
    config([
        'services.supabase.image_max_bytes' => 200 * 1024,
        'services.supabase.image_max_dimension' => 800,
    ]);

    $file = UploadedFile::fake()->image('foto.jpg', 2000, 2000);
    $service = new SupabaseService;
    $method = new ReflectionMethod($service, 'prepareImage');
    $method->setAccessible(true);

    [$path, $name, $temporary] = $method->invoke($service, $file);

    expect(filesize($path))->toBeLessThanOrEqual(200 * 1024)
        ->and($name)->toEndWith('.jpg')
        ->and($temporary)->toBeTrue();

    if ($temporary) {
        @unlink($path);
    }
});
