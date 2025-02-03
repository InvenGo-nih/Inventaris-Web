<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Inventaris;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BorrowController extends Controller
{
    //
    public function index(){
        $data = Borrow::all();
        return view('borrow.index', compact('data'));
    }

    public function form(Request $request, $id = null){
        $data = $id ? Borrow::findorfail($request->id) : new Borrow();
        $users = User::all();
        $inventaris = Inventaris::where('is_borrow', 0)->get(); 
        return view('borrow.form',compact('data', 'inventaris', 'users'));
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
        
        $data = new Borrow();
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

        $data = Borrow::findOrFail($id);
        $data->name = $request->name;

        // Periksa apakah ada file gambar baru
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Hapus gambar lama jika ada
            // if ($data->image && Storage::disk('public')->exists($data->image)) {
            //     Storage::disk('public')->delete($data->image);
            //     $imagePath = $image->store('images', 'public');
            //     $data->image = $imagePath;
            // }

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
}
