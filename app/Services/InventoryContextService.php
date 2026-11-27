<?php

namespace App\Services;

use App\Models\Borrow;
use App\Models\GroupInventaris;
use App\Models\Inventaris;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryContextService
{
    public function build(?User $user): array
    {
        $cacheKey = 'chatbot:context:'.($user?->id ?? 'guest');

        return Cache::remember($cacheKey, 60, function () use ($user) {
            $today = now()->toDateString();

            $totalBaris = Inventaris::query()->count();
            $totalUnit = (int) Inventaris::query()->sum('quantity');

            $normalBaris = Inventaris::query()->where('condition', 'Normal')->count();
            $normalUnit = (int) Inventaris::query()->where('condition', 'Normal')->sum('quantity');
            $rusakBaris = Inventaris::query()->where('condition', 'Rusak')->count();
            $rusakUnit = (int) Inventaris::query()->where('condition', 'Rusak')->sum('quantity');

            $contohKerusakan = Inventaris::query()
                ->where('condition', 'Rusak')
                ->whereNotNull('broken_description')
                ->where('broken_description', '!=', '')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get(['name', 'broken_description'])
                ->map(fn (Inventaris $item) => [
                    'barang' => $item->name,
                    'kerusakan' => $item->broken_description,
                ])
                ->values()
                ->all();

            $groupInventaris = GroupInventaris::query()
                ->leftJoin('inventaris', function ($join) {
                    $join->on('inventaris.group_inventaris_id', '=', 'group_inventaris.id')
                        ->whereNull('inventaris.deleted_at');
                })
                ->groupBy('group_inventaris.id', 'group_inventaris.name', 'group_inventaris.type')
                ->orderBy('group_inventaris.name')
                ->get([
                    'group_inventaris.name',
                    'group_inventaris.type',
                    DB::raw('COUNT(inventaris.id) as jumlah_baris'),
                    DB::raw('COALESCE(SUM(inventaris.quantity), 0) as total_unit'),
                    DB::raw("COALESCE(SUM(CASE WHEN inventaris.condition = 'Normal' THEN inventaris.quantity ELSE 0 END), 0) as normal_unit"),
                    DB::raw("COALESCE(SUM(CASE WHEN inventaris.condition = 'Rusak' THEN inventaris.quantity ELSE 0 END), 0) as rusak_unit"),
                ])
                ->map(fn ($group) => [
                    'nama' => $group->name,
                    'tipe' => $group->type,
                    'jumlah_baris' => (int) $group->jumlah_baris,
                    'total_unit' => (int) $group->total_unit,
                    'normal_unit' => (int) $group->normal_unit,
                    'rusak_unit' => (int) $group->rusak_unit,
                ])
                ->values()
                ->all();

            $context = [
                'tanggal_hari_ini' => $today,
                'inventaris' => [
                    'total_baris' => $totalBaris,
                    'total_unit' => $totalUnit,
                ],
                'kondisi' => [
                    'normal' => [
                        'jumlah_baris' => $normalBaris,
                        'total_unit' => $normalUnit,
                    ],
                    'rusak' => [
                        'jumlah_baris' => $rusakBaris,
                        'total_unit' => $rusakUnit,
                    ],
                    'contoh_kerusakan' => $contohKerusakan,
                ],
                'group_inventaris' => $groupInventaris,
                'referensi' => [
                    'role_dikenal' => ['admin', 'teknisi', 'guru'],
                    'grup_permission' => ['inventaris', 'peminjaman', 'scan', 'user', 'role', 'dashboard', 'cek', 'chatbot'],
                ],
            ];

            if ($this->userHasPermission($user, 'VIEW_BORROW')) {
                $context['peminjaman'] = [
                    'jumlah_dipinjam' => Borrow::query()->where('status', 'Dipinjam')->count(),
                    'jumlah_dikembalikan' => Borrow::query()->where('status', 'Dikembalikan')->count(),
                    'jumlah_terlambat' => Borrow::query()
                        ->where('status', 'Dipinjam')
                        ->whereNotNull('max_return_date')
                        ->where('max_return_date', '<', $today)
                        ->count(),
                    'terakhir' => Borrow::query()
                        ->with('inventaris:id,name')
                        ->orderByDesc('date_borrow')
                        ->orderByDesc('id')
                        ->limit(10)
                        ->get()
                        ->map(fn (Borrow $borrow) => [
                            'peminjam' => $borrow->borrow_by,
                            'barang' => $borrow->inventaris?->name,
                            'quantity' => (int) $borrow->quantity,
                            'tanggal_pinjam' => $borrow->date_borrow?->toDateString(),
                            'batas_pengembalian' => $borrow->max_return_date?->toDateString(),
                            'tanggal_kembali' => $borrow->date_back?->toDateString(),
                            'status' => $borrow->status,
                        ])
                        ->values()
                        ->all(),
                ];
            }

            if ($this->userHasPermission($user, 'VIEW_USERS')) {
                $context['users'] = [
                    'total' => User::query()->count(),
                    'daftar' => User::query()
                        ->with('role')
                        ->orderBy('name')
                        ->limit(50)
                        ->get(['id', 'name', 'email', 'role_id'])
                        ->map(fn (User $listedUser) => [
                            'nama' => $listedUser->name,
                            'email' => $listedUser->email,
                            'role' => $listedUser->role?->name,
                        ])
                        ->values()
                        ->all(),
                ];
            }

            return $context;
        });
    }

    public static function forgetFor(?User $user = null): void
    {
        Cache::forget('chatbot:context:'.($user?->id ?? 'guest'));
    }

    private function userHasPermission(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        try {
            return (bool) $user->hasAnyPermission($permission);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
