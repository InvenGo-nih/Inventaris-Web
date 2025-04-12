<?php

namespace App\Http\Controllers;

use App\DataTables\CekDataTable;
use App\Models\Inventaris;
use App\Models\InventarisLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CekController extends Controller
{

    public function index(CekDataTable $dataTable)
    {
        // return view('cekInventaris.index');
        return $dataTable->render('cekInventaris.index');
    }

    public function cek(Request $request, $id)
    {   
        $data = Inventaris::findorFail($id);
        $location = InventarisLocation::all();
        return view('cekInventaris.form', compact('data', 'location'));
    }

    public function update(Request $request, $id)
    {
        // Validasi data yang diterima
        $validate = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg|max:2048', // Gambar tidak wajib saat update
            // 'specification' => 'required',
            'condition' => 'required',
        ], [
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, atau jpg.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            // 'specification.required' => 'Spesifikasi harus diisi.',
            'condition.required' => 'Kondisi harus diisi.',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $data = Inventaris::findOrFail($id);
        $data->name = $request->name;

        $data->specification = $request->specification;
        $data->condition = $request->condition;
        // $data->status = $request->status;
        $data->location = $request->location;
        $data->qr_link = route('test_qr', ['id' => $data->id]); // Perbarui qr_link
        $data->broken_description = $request->broken_description;
        $data->type = $request->type;
        $data->quantity = $request->quantity;
        $data->group_inventaris_id = $request->group_inventaris_id;
        $data->save();

        return redirect()->route('cek.index')->with('success', 'Pengecekan berhasil disimpan');
    }

}
