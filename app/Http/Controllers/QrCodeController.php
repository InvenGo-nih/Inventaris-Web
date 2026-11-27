<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QrCodeController extends Controller
{
    public function showScanPage()
    {
        return view('qr_scan');
    }

    public function processScan(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => ['required', 'string', 'url', 'max:2048'],
        ], [
            'qr_data.required' => 'Data QR tidak ditemukan.',
            'qr_data.url' => 'QR code tidak berisi URL yang valid.',
        ]);

        $url = $validated['qr_data'];
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        $allowedHosts = array_filter([
            strtolower($request->getHost()),
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
        ]);

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || ! in_array($host, $allowedHosts, true)) {
            throw ValidationException::withMessages([
                'qr_data' => 'QR code bukan berasal dari aplikasi ini.',
            ]);
        }

        return redirect()->away($url);
    }
}
