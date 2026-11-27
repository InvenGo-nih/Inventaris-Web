<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DasboardController extends Controller
{
    public function index(Request $request)
    {
        [$month, $year] = $this->validatedFilters($request);

        return view('dashboard', [
            'data' => $this->dashboardData($month, $year),
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'months' => [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ],
            'years' => $this->availableYears($year),
        ]);
    }

    public function chartData(Request $request)
    {
        [$month, $year] = $this->validatedFilters($request);

        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $this->dashboardData($month, $year),
        ]);
    }

    private function dashboardData(int $month, int $year): array
    {
        $summary = Cache::remember('dashboard:chart', now()->addMinute(), function () {
            return [
                'InventarisCount' => Inventaris::count(),
                'BorrowCount' => Borrow::where('status', 'Dipinjam')->count(),
            ];
        });

        $chartQuery = Inventaris::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        return array_merge($summary, [
            'NormalCount' => (clone $chartQuery)->where('condition', 'Normal')->count(),
            'RusakCount' => (clone $chartQuery)->where('condition', 'Rusak')->count(),
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,'.now()->year],
        ]);

        return [
            (int) ($filters['month'] ?? now()->month),
            (int) ($filters['year'] ?? now()->year),
        ];
    }

    private function availableYears(int $selectedYear): array
    {
        $earliestRecord = Inventaris::min('created_at');
        $firstYear = $earliestRecord
            ? Carbon::parse($earliestRecord)->year
            : now()->year;
        $firstYear = min($firstYear, $selectedYear);

        return array_reverse(range($firstYear, now()->year));
    }
}
