<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupInventarisRequest;
use App\Models\GroupInventaris;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GroupInventarisController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index(Request $request)
    {
        $query = GroupInventaris::withCount([
            'inventaris as jumlah',
            'inventaris as normal' => fn ($q) => $q->where('condition', 'Normal'),
            'inventaris as rusak' => fn ($q) => $q->where('condition', 'Rusak'),
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate(config('inventaris.groups_per_page', 8));

        return view('groupInventaris.index', compact('data'));
    }

    public function form($id = null)
    {
        $data = $id ? GroupInventaris::findOrFail($id) : new GroupInventaris;

        return view('groupInventaris.form', compact('data'));
    }

    public function store(GroupInventarisRequest $request)
    {
        $validated = $request->validated();
        $file = $request->file('image');
        $filePath = 'inventaris_images/'.Str::uuid().'.'.$file->extension();

        try {
            $this->supabase->uploadFile($file, $filePath);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar grup inventaris.');
        }

        try {
            GroupInventaris::create([
                'name' => $validated['name'],
                'image' => $filePath,
                'type' => $validated['type'],
            ]);
        } catch (\Throwable $e) {
            $this->supabase->deleteFile($filePath);
            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan grup inventaris.');
        }

        Cache::forget('dashboard:chart');

        return redirect()->route('inventaris.index')->with('success', 'Grup barang berhasil ditambah');
    }

    public function update(GroupInventarisRequest $request, $id)
    {
        $validated = $request->validated();
        $data = GroupInventaris::findOrFail($id);
        $oldImage = $data->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $newImage = 'inventaris_images/'.Str::uuid().'.'.$file->extension();

            try {
                $this->supabase->uploadFile($file, $newImage);
            } catch (\Throwable $e) {
                report($e);

                return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar grup inventaris.');
            }
        }

        $data->name = $validated['name'];
        $data->type = $validated['type'];

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

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui grup inventaris.');
        }

        if ($newImage && $oldImage) {
            $this->supabase->deleteFile($oldImage);
        }

        Cache::forget('dashboard:chart');

        return redirect()->route('inventaris.index')->with('success', 'Grup barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = GroupInventaris::findOrFail($id);

        if ($data->inventaris()->withTrashed()->exists()) {
            return redirect()->route('inventaris.index')
                ->with('error', 'Grup barang tidak dapat dihapus karena masih memiliki barang.');
        }

        try {
            $image = $data->image;
            $data->delete();

            if ($image) {
                $this->supabase->deleteFile($image);
            }

            Cache::forget('dashboard:chart');

            return redirect()->route('inventaris.index')->with('success', 'Grup barang berhasil dihapus');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('inventaris.index')->with('error', 'Terjadi kesalahan saat menghapus grup barang');
        }
    }
}
