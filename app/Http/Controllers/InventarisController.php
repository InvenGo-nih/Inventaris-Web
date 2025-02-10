<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

            return view('welcome', compact('data', 'jumlah'));
        }
        $data = Inventaris::paginate(5);
        return view('welcome', compact('data', 'jumlah'));
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
        $data->save();
        
        // Perbarui qr_link dengan ID yang baru saja disimpan
        $data->qr_link = route('test_qr', ['id' => $data->id]);
        $data->save(); // Simpan kembali dengan qr_link

        return redirect()->route('home')->with('success', 'Data berhasil disimpan');
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

        $data->specification = $request->specification;
        $data->condition = $request->condition;
        $data->status = $request->status;
        $data->qr_link = route('test_qr', ['id' => $data->id]); // Perbarui qr_link
        $data->save();

        return redirect()->route('home')->with('success', 'Data berhasil disimpan');
    }

    public function show($id)
    {
        $data = Inventaris::findOrFail($id);
        return view('inventaris.show', compact('data'));
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

    return redirect()->route('home')->with('success', 'Data inventaris berhasil dihapus');

        
    }
}
