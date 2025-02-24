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
        $NormalCount = Inventaris::where('condition','Normal')->count();
        $RusakCount = Inventaris::where('condition','Rusak')->count();
        $data = [
            'InventarisCount' => $InventarisCount,
            'BorrowCount' => $BorrowCount,
            'NormalCount' => $NormalCount,
            'RusakCount' => $RusakCount
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
