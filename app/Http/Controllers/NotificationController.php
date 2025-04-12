<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function overdueNotifications()
    {
        $today = Carbon::now()->startOfDay();
        
        $overdueBorrows = Borrow::with(['inventaris'])
            ->where('status', 'Dipinjam')
            ->where('max_return_date', '<', $today)
            ->latest()
            ->get();

        return view('notifications.overdue', compact('overdueBorrows'));
    }
}
