<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Inventaris;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BorrowController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        $data = Borrow::with(['user', 'inventaris'])->get();
        return view('borrow.index', compact('data'));
    }

    public function form($id = null)
    {
        $data = $id ? Borrow::findOrFail($id) : new Borrow();
        $users = User::all();
        $inventaris = Inventaris::where('is_borrow', 0)->get();
        return view('borrow.form', compact('data', 'inventaris', 'users'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'inventaris_id' => 'required|exists:inventaris,id',
            'date_borrow'   => 'required|date',
            'date_back'     => 'nullable|date|after_or_equal:date_borrow',
            'status'        => 'required',
            'img_borrow'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $data = new Borrow();
        $data->user_id = $request->user_id;
        $data->inventaris_id = $request->inventaris_id;
        $data->date_borrow = $request->date_borrow;
        $data->date_back = $request->date_back;
        $data->status = $request->status;

        // Upload gambar jika ada
        if ($request->hasFile('img_borrow')) {
            $file = $request->file('img_borrow');
            $filePath = 'borrow_images/' . time() . '_' . $file->getClientOriginalName();
            $this->supabase->uploadFile($file, $filePath);
            $data->img_borrow = $filePath;
        }

        $data->save();

        // Update status barang yang dipinjam
        Inventaris::where('id', $request->inventaris_id)->update([
            'is_borrow' => $request->status === 'borrowed' ? 1 : 0
        ]);

        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $data = Borrow::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'inventaris_id' => 'required|exists:inventaris,id',
            'date_borrow'   => 'required|date',
            'date_back'     => 'nullable|date|after_or_equal:date_borrow',
            'status'        => 'required',
            'img_borrow'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // Hapus gambar lama jika ada gambar baru
        if ($request->hasFile('img_borrow')) {
            if ($data->img_borrow) {
                $this->supabase->deleteFile($data->img_borrow);
            }

            $file = $request->file('img_borrow');
            $filePath = 'borrow_images/' . time() . '_' . $file->getClientOriginalName();
            $this->supabase->uploadFile($file, $filePath);
            $data->img_borrow = $filePath;
        }

        // Update status inventaris berdasarkan perubahan status peminjaman
        if ($request->status === 'returned' && $data->status !== 'returned') {
            Inventaris::where('id', $data->inventaris_id)->update(['is_borrow' => 0]);
        } elseif ($request->status === 'borrowed' && $data->status !== 'borrowed') {
            Inventaris::where('id', $data->inventaris_id)->update(['is_borrow' => 1]);
        }

        $data->update([
            'user_id'       => $request->user_id,
            'inventaris_id' => $request->inventaris_id,
            'date_borrow'   => $request->date_borrow,
            'date_back'     => $request->date_back,
            'status'        => $request->status,
            'img_borrow'    => $data->img_borrow
        ]);

        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = Borrow::findOrFail($id);

        // Hapus gambar jika ada
        if ($data->img_borrow) {
            $this->supabase->deleteFile($data->img_borrow);
        }

        // Update status barang
        Inventaris::where('id', $data->inventaris_id)->update(['is_borrow' => 0]);

        $data->delete();
        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil dihapus');
    }
}
