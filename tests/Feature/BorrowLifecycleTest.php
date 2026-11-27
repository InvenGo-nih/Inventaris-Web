<?php

use App\Models\Borrow;
use App\Models\GroupInventaris;
use App\Models\Inventaris;
use App\Models\User;
use App\Services\SupabaseService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->mock(SupabaseService::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFile')->andReturnTrue();
        $mock->shouldReceive('deleteFile')->andReturnTrue();
    });
});

it('decrements stock when borrowed and restores it when returned', function () {
    $admin = User::factory()->create();
    $item = Inventaris::factory()->create([
        'group_inventaris_id' => GroupInventaris::factory(),
        'quantity' => 2,
        'is_borrow' => false,
    ]);

    $payload = [
        'borrow_by' => 'Peminjam Test',
        'inventaris_id' => $item->id,
        'quantity' => 1,
        'date_borrow' => now()->toDateString(),
        'max_return_date' => now()->addDays(7)->toDateString(),
        'status' => 'Dipinjam',
        'img_borrow' => UploadedFile::fake()->image('borrow.jpg'),
    ];

    $this->actingAs($admin)
        ->post(route('borrow.store'), $payload)
        ->assertRedirect(route('borrow.index'));

    $borrow = Borrow::firstOrFail();

    expect($borrow->quantity)->toBe(1);

    $item->refresh();
    expect($item->quantity)->toBe(1)
        ->and($item->is_borrow)->toBeTrue();

    $this->actingAs($admin)
        ->put(route('borrow.update', $borrow->id), [
            'borrow_by' => $borrow->borrow_by,
            'date_borrow' => $borrow->date_borrow->toDateString(),
            'date_back' => now()->toDateString(),
            'max_return_date' => $borrow->max_return_date->toDateString(),
            'status' => 'Dikembalikan',
        ])
        ->assertRedirect(route('borrow.index'));

    $item->refresh();
    expect($item->quantity)->toBe(2)
        ->and($item->is_borrow)->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('borrow.delete', $borrow->id))
        ->assertRedirect(route('borrow.index'));

    $this->assertSoftDeleted('borrows', ['id' => $borrow->id]);

    $item->refresh();
    expect($item->quantity)->toBe(2);
});

it('rejects borrowing an item that is already borrowed', function () {
    $admin = User::factory()->create();
    $item = Inventaris::factory()->create([
        'quantity' => 1,
        'is_borrow' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('borrow.store'), [
            'borrow_by' => 'Peminjam Test',
            'inventaris_id' => $item->id,
            'quantity' => 1,
            'date_borrow' => now()->toDateString(),
            'max_return_date' => now()->addDays(7)->toDateString(),
            'status' => 'Dipinjam',
            'img_borrow' => UploadedFile::fake()->image('borrow.jpg'),
        ])
        ->assertSessionHasErrors('inventaris_id');

    expect(Borrow::count())->toBe(0);
});

it('does not delete an active borrow', function () {
    $admin = User::factory()->create();
    $borrow = Borrow::factory()->create([
        'inventaris_id' => Inventaris::factory()->create(['is_borrow' => true]),
        'status' => 'Dipinjam',
    ]);

    $this->actingAs($admin)
        ->delete(route('borrow.delete', $borrow->id))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted('borrows', ['id' => $borrow->id]);
});
