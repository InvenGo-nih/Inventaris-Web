<?php

namespace App\Http\Controllers;

use App\DataTables\CekDataTable;
use App\Http\Requests\UpdateCekRequest;
use App\Models\Inventaris;
use App\Services\InventoryContextService;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CekController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index(CekDataTable $dataTable)
    {
        return $dataTable->render('cekInventaris.index');
    }

    public function cek($id)
    {
        $data = Inventaris::findOrFail($id);

        return view('cekInventaris.form', compact('data'));
    }

    public function update(UpdateCekRequest $request, $id)
    {
        $validated = $request->validated();
        $data = Inventaris::findOrFail($id);
        $oldImage = $data->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $newImage = 'inventaris_images/'.Str::uuid().'.'.$file->extension();

            try {
                $this->supabase->uploadFile($file, $newImage);
            } catch (\Throwable $e) {
                report($e);

                return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar inventaris.');
            }
        }

        $data->condition = $validated['condition'];
        $data->broken_description = $validated['broken_description'] ?? null;
        $data->qr_link = route('test_qr', ['id' => $data->id]);

        if ($newImage) {
            $data->image = $newImage;
        }

        try {
            $data->save();
        } catch (\Throwable $e) {
            if ($newImage) {
                $this->supabase->deleteFile($newImage);
            }

            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan pengecekan.');
        }

        if ($newImage && $oldImage) {
            $this->supabase->deleteFile($oldImage);
        }

        Cache::forget("inventaris:stats:{$data->group_inventaris_id}");
        Cache::forget('dashboard:chart');
        InventoryContextService::forgetFor($request->user());

        return redirect()->route('cek.index')->with('success', 'Pengecekan berhasil disimpan');
    }
}
