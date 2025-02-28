<?php

namespace App\Http\Controllers;

use App\Models\InventarisLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventarisLocationController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $data = InventarisLocation::paginate(10);
        $form = $id ? InventarisLocation::findorFail($request->id) : new InventarisLocation();
        return view('inventaris_location.index', compact(['data', 'form']));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'location' => 'required',
        ], [
            'location.required' => 'Lokasi harus diisi.',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $data = new InventarisLocation();
        $data->location = $request->location;
        $data->save();

        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'location' => 'required',
        ], [
            'location.required' => 'Lokasi harus diisi.',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $data = InventarisLocation::findOrFail($id);
        $data->location = $request->location;
        $data->save();  

        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = InventarisLocation::findOrFail($id);
        $data->delete();
        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil dihapus');
    }
}
