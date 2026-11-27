<?php

namespace App\Http\Controllers;

use App\DataTables\InventarisLocationDataTable;
use App\Http\Requests\InventarisLocationRequest;
use App\Models\InventarisLocation;

class InventarisLocationController extends Controller
{
    public function index(InventarisLocationDataTable $dataTable)
    {
        return $dataTable->render('inventaris_location.index');
    }

    public function store(InventarisLocationRequest $request)
    {
        InventarisLocation::create($request->validated());

        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil disimpan');
    }

    public function update(InventarisLocationRequest $request, $id)
    {
        $data = InventarisLocation::findOrFail($id);
        $data->update($request->validated());

        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = InventarisLocation::findOrFail($id);
        $data->delete();

        return redirect()->route('inventaris.location.index')->with('success', 'Lokasi inventaris berhasil dihapus');
    }
}
