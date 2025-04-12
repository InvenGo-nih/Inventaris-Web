<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueBorrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrows:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue borrows and log them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');
        
        $overdueBorrows = Borrow::where('status', 'Dipinjam')
            ->where('max_return_date', '<', $today)
            ->get();

        foreach ($overdueBorrows as $borrow) {
            Log::warning('Peminjaman terlambat', [
                'id' => $borrow->id,
                'borrow_by' => $borrow->borrow_by,
                'inventaris_id' => $borrow->inventaris_id,
                'max_return_date' => $borrow->max_return_date,
                'days_overdue' => now()->diffInDays($borrow->max_return_date)
            ]);
        }

        $this->info('Pengecekan peminjaman terlambat selesai. Total: ' . $overdueBorrows->count());
    }
}
