<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function showScanPage()
    {
        return view('qr_scan'); // View untuk halaman scan QR
    }

    public function processScan(Request $request)
    {
        // Ambil data QR Code dari input
        $qrCodeData = $request->input('qr_data');

        // Redirect ke URL yang terdapat dalam $qrCodeData
        return redirect()->away($qrCodeData); // Mengarahkan ke URL yang diambil dari QR Code
    }
}
