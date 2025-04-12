<?php

namespace App\Http\Controllers;

use App\Models\GroupInventaris;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupInventarisController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }
    public function index(Request $request)
    {
        if (request()->has('search')) {
            $search = $request->input('search');

            $data = GroupInventaris::where('name', 'like', "%$search%")
                ->orWhere('type', 'like', "%$search%")
                ->latest()->paginate(8);

            return view('groupInventaris.index', compact('data'));
        }
        $data = GroupInventaris::latest()->paginate(8);
        return view('groupInventaris.index', compact('data'));
    }

    public function form(Request $request, $id = null)
    {
        $data = $id ? GroupInventaris::findorFail($request->id) : new GroupInventaris();
        return view('groupInventaris.form', compact('data'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required'
        ], [
            'name.required' => 'Nama barang harus diisi.',
            'image.required' => 'Gambar harus diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, atau jpg.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'type.required' => 'Tipe barang harus diisi.'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $file = $request->file('image');
        $filePath = 'inventaris_images/' . time() . '_' . $file->getClientOriginalName();
        $this->supabase->uploadFile($file, $filePath);

        $data = new GroupInventaris();
        $data->name = $request->name;
        $data->image = $filePath; // Hanya menyimpan nama file
        $data->type = $request->type;
        $data->save();

        return redirect()->route('inventaris.index')->with('success', 'Inventaris berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        // Validasi data yang diterima
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required'
        ], [
            'name.required' => 'Nama barang harus diisi.',
            'image.required' => 'Gambar harus diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, atau jpg.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'type.required' => 'Tipe barang harus diisi.'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $data = GroupInventaris::findOrFail($id);
        $data->name = $request->name;

        // Periksa apakah ada file gambar baru
        if ($request->hasFile('image')) {
            if ($data->image) {
                $this->supabase->deleteFile($data->image);
            }

            $file = $request->file('image');
            $filePath = 'inventaris_images/' . time() . '_' . $file->getClientOriginalName();
            $this->supabase->uploadFile($file, $filePath);
            $data->image = $filePath;
        }            
        
        $data->type = $request->type;
        $data->save();

        return redirect()->route('inventaris.index')->with('success', 'Inventaris berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $data = GroupInventaris::findOrFail($id);

            // Hapus gambar jika ada
            if ($data->img_borrow) {
                $this->supabase->deleteFile($data->img_borrow);
            }

            // Hapus data inventaris
            $data->delete();

            return redirect()->route('inventaris.index')->with('success', 'Inventaris berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('inventaris.index')->with('error', 'Inventaris tidak dapat dihapus karena masih dalam peminjaman');
            }
            return redirect()->route('inventaris.index')->with('error', 'Terjadi kesalahan saat menghapus inventaris');
        }
    }
}
