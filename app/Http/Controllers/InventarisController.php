<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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
        return view('inventaris.form', compact('data'));
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
        
        $data = new Inventaris();
        $data->name = $request->name;
        $data->image = $imagePath; // Hanya menyimpan nama file
        $data->specification = $request->specification;
        $data->condition = $request->condition;
        $data->status = $request->status;
        $data->location = $request->location ?? 'Tidak Diketahui';
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
        $data->qr_link = route('test_qr', ['id' => $data->id]); // Perbarui qr_link
        $data->serial_number = $serialNumber ;
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
        $data = Inventaris::all();

        foreach ($data as $item) {
            // Gunakan format SVG agar tidak memerlukan Imagick
            $qrCode = QrCode::format('svg')
                ->size(100)
                ->errorCorrection('H')
                ->generate($item->qr_link);

            // Simpan QR Code dalam variabel sebagai string (tanpa file)
            $item->qr_code = $qrCode;
        }

        // Load view PDF
        $pdf = Pdf::loadView('inventaris.pdf', compact('data'));

        return $pdf->download('inventaris.pdf');
    }
}
