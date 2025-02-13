<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Inventaris;
use Illuminate\Http\Request;

class DasboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function chartData()
    {
        $InventarisCount = Inventaris::count();
        $BorrowCount = Borrow::count();
        $data = [
            'InventarisCount' => $InventarisCount,
            'BorrowCount' => $BorrowCount,
        ];
        return response()->json(
            [
                'status' => 200,
                'success' => true,
                'data' => $data
            ]
        );
    }
}
