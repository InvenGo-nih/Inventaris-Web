<?php

namespace App\Http\Controllers;

use App\Models\Borrow;

class NotificationController extends Controller
{
    public function overdueNotifications()
    {
        $today = now()->startOfDay();

        $overdueBorrows = Borrow::with(['inventaris' => fn ($query) => $query->withTrashed()])
            ->where('status', 'Dipinjam')
            ->whereNotNull('max_return_date')
            ->where('max_return_date', '<', $today->toDateString())
            ->orderBy('max_return_date')
            ->paginate(config('inventaris.overdue_per_page', 20));

        return view('notifications.overdue', compact('overdueBorrows'));
    }
}
