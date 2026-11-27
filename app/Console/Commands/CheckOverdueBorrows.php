<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueBorrows extends Command
{
    protected $signature = 'borrows:check-overdue';

    protected $description = 'Check for overdue borrows and log them';

    public function handle()
    {
        $today = now()->startOfDay();
        $total = 0;

        Borrow::where('status', 'Dipinjam')
            ->whereNotNull('max_return_date')
            ->where('max_return_date', '<', $today->toDateString())
            ->chunkById(500, function ($borrows) use ($today, &$total) {
                foreach ($borrows as $borrow) {
                    $total++;

                    $dueDate = $borrow->max_return_date->copy()->startOfDay();

                    Log::warning('Peminjaman terlambat', [
                        'id' => $borrow->id,
                        'borrow_by' => $borrow->borrow_by,
                        'inventaris_id' => $borrow->inventaris_id,
                        'max_return_date' => $dueDate->toDateString(),
                        'days_overdue' => (int) $dueDate->diffInDays($today, true),
                    ]);
                }
            });

        $this->info('Pengecekan peminjaman terlambat selesai. Total: '.$total);
    }
}
