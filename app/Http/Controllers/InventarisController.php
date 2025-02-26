<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\InventarisLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use TCPDF;
use illuminate\Support\Str;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $jumlah = Inventaris::count();
        if (request()->has('search')) {
            $search = $request->input('search');

            $data = Inventaris::where('name', 'like', "%$search%")
                ->orWhere('specification', 'like', "%$search%")
                ->orWhere('condition', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->paginate(10);

            return view('inventaris.index', compact('data', 'jumlah'));
        }
        $data = Inventaris::paginate(5);
        return view('inventaris.index', compact('data', 'jumlah'));
    }

    public function form(Request $request, $id = null)
    {
        $data = $id ? Inventaris::findorFail($request->id) : new Inventaris();
        $location = InventarisLocation::all();
        return view('inventaris.form', compact(['data','location']));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required|image',
            'specification' => 'required',
            'condition' => 'required',
            'status' => 'required',
            'location' => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // Upload image ke public/storage/images
        $image = $request->file('image');
        $imagePath = $image->store('images', 'public'); // Mengembalikan path lengkap di storage/public
        $serialNumber = 'SN-' . str::upper(Str::random(10));

        $data = new Inventaris();
        $data->name = $request->name;
        $data->image = $imagePath; // Hanya menyimpan nama file
        $data->specification = $request->specification;
        $data->condition = $request->condition;
        $data->status = $request->status;
        $data->location = $request->location ?? 'Tidak Diketahui';
        $data->serial_number = $serialNumber;
        $data->broken_description = $request->broken_description;
        $data->save();
        
        // Perbarui qr_link dengan ID yang baru saja disimpan
        $data->qr_link = route('test_qr', ['id' => $data->id]);
        $data->save(); // Simpan kembali dengan qr_link

        return redirect()->route('inventaris.index')->with('success', 'Data berhasil disimpan');
    }


    public function update(Request $request, $id)
    {
        // Validasi data yang diterima
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'image', // Gambar tidak wajib saat update
            'specification' => 'required',
            'condition' => 'required',
            'status' => 'required',
            'location' => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $data = Inventaris::findOrFail($id);
        $data->name = $request->name;

        // Periksa apakah ada file gambar baru
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Hapus gambar lama jika ada
            if ($data->image && Storage::disk('public')->exists($data->image)) {
                Storage::disk('public')->delete($data->image);
                $imagePath = $image->store('images', 'public');
                $data->image = $imagePath;
            }

            // Upload gambar baru
            $imagePath = $image->store('images', 'public'); // Simpan ke storage/app/public/images

            // Simpan path gambar baru ke database
            $data->image = $imagePath;
        }            
        
        $serialNumber = 'SN-' . str::upper(Str::random(10));

        $data->specification = $request->specification;
        $data->condition = $request->condition;
        $data->status = $request->status;
        $data->location = $request->location;
        $data->qr_link = route('test_qr', ['id' => $data->id]); // Perbarui qr_link
        $data->serial_number = $serialNumber ;
        $data->broken_description = $request->broken_description;
        $data->save();

        return redirect()->route('inventaris.index')->with('success', 'Data berhasil disimpan');
    }

    public function show($id)
    {
        $data = Inventaris::findOrFail($id);
        return view('inventaris.show', compact('data'));
    }
    public function showQr($id)
    {
        $data = Inventaris::findOrFail($id);
        return view('inventaris.showQrData', compact('data'));
    }

    public function destroy($id)
    {
        $data = Inventaris::findOrFail($id);

        // Hapus gambar jika ada
        if ($data->image && Storage::disk('public')->exists($data->image)) {
            Storage::disk('public')->delete($data->image);
        }

        // Hapus data inventaris
        $data->delete();

        return redirect()->route('inventaris.index')->with('success', 'Data inventaris berhasil dihapus');
    }

    public function downloadPDF()
    {
        // Ambil semua data dari database
        $inventaris = Inventaris::all();

        // Buat instance TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Rifki');
        $pdf->SetTitle('Laporan Inventaris');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10); // Auto page break dengan margin bawah 10mm
        $pdf->AddPage();

        // Tambahkan judul
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Laporan Inventaris', 0, 1, 'C');
        $pdf->Ln(5); // Spasi setelah judul

        // Header tabel
        $columnWidths = [50, 50, 50, 40]; // Lebar masing-masing kolom
        $lineHeight = 30; // Tinggi baris header

        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($columnWidths[0], $lineHeight, 'Nama Barang', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[1], $lineHeight, 'Kondisi', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[2], $lineHeight, 'Spesifikasi', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[3], $lineHeight, 'QR Code', 1, 1, 'C', true);

        // Loop data inventaris
        foreach ($inventaris as $index => $item) {
            // Jika sudah 7 item di halaman ini, buat halaman baru
            if (($index > 0) && ($index % 7 == 0)) {
                $pdf->AddPage();
        
                // Tambahkan ulang header di halaman baru
                $pdf->SetFillColor(200, 200, 200);
                $pdf->Cell($columnWidths[0], $lineHeight, 'Nama Barang', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[1], $lineHeight, 'Kondisi', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[2], $lineHeight, 'Spesifikasi', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[3], $lineHeight, 'QR Code', 1, 1, 'C', true);
            }
        
            // Isi Data
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell($columnWidths[0], $lineHeight, $item->name, 1, 0, 'L');
            $pdf->Cell($columnWidths[1], $lineHeight, $item->condition, 1, 0, 'C');
            $pdf->Cell($columnWidths[2], $lineHeight, $item->specification, 1, 0, 'L');
        
            // Simpan posisi awal untuk QR Code
            $xPos = $pdf->GetX();
            $yPos = $pdf->GetY();
        
            // Cell kosong untuk QR Code agar kotaknya tetap rapi
            $pdf->Cell($columnWidths[3], $lineHeight, '', 1, 1, 'C'); // Cell ini hanya untuk kotak
        
            // Buat QR Code lebih kecil & di tengah cell
            $qrSize = 15; // Ukuran QR Code lebih kecil
            $qrX = $xPos + ($columnWidths[3] - $qrSize) / 2; // Posisi tengah
            $qrY = $yPos + ($lineHeight - $qrSize) / 2; // Posisi tengah
            $pdf->write2DBarcode($item->qr_link ?? 'No QR', 'QRCODE,M', $qrX, $qrY, $qrSize, $qrSize);
        }
        

        // Output PDF
        return response($pdf->Output('inventaris.pdf', 'D'))
            ->header('Content-Type', 'application/pdf');
    }
    
}
