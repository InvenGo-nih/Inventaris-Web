<?php

namespace App\Http\Controllers;

use App\DataTables\BorrowDataTable;
use App\Models\Borrow;
use App\Models\Inventaris;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BorrowController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(BorrowDataTable $dataTable)
    {
        $data = Borrow::with('inventaris')->latest()->paginate(10);
        // return view('borrow.index', compact('data'));
        return $dataTable->render('borrow.index', compact('data'));
    }

    public function form($id = null)
    {
        $data = $id ? Borrow::findOrFail($id) : new Borrow();
        $users = User::all();
        $inventaris = Inventaris::all(); // Ubah untuk menampilkan semua inventaris
        return view('borrow.form', compact('data', 'inventaris', 'users'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'borrow_by'     => 'required',
            'inventaris_id' => 'required|exists:inventaris,id',
            'quantity'      => 'nullable|integer|min:1',
            'date_borrow'   => 'required|date',
            'date_back'     => 'nullable|date|after_or_equal:date_borrow',
            'status'        => 'required',
            'img_borrow'    => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // Cek ketersediaan jumlah barang
        $inventaris = Inventaris::findOrFail($request->inventaris_id);
        $requestedQuantity = $request->quantity ?? 1;
        
        if ($requestedQuantity > $inventaris->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Jumlah yang diminta melebihi stok yang tersedia'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $data = new Borrow();
            $data->borrow_by = $request->borrow_by;
            $data->inventaris_id = $request->inventaris_id;
            $data->quantity = $requestedQuantity;
            $data->date_borrow = $request->date_borrow;
            $data->date_back = $request->date_back;
            $data->status = $request->status;

            // Upload gambar dengan nama yang di-hash
            $file = $request->file('img_borrow');
            $extension = $file->getClientOriginalExtension();
            $hashedName = md5($file->getClientOriginalName() . time()) . '.' . $extension;
            $filePath = 'borrow_images/' . $hashedName;
            $this->supabase->uploadFile($file, $filePath);
            $data->img_borrow = $filePath;

            $data->save();

            // Update jumlah barang yang tersedia
            $inventaris->quantity -= $requestedQuantity;
            $inventaris->save();

            DB::commit();
            return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data');
        }
    }

    public function update(Request $request, $id)
    {
        $data = Borrow::findOrFail($id);
        $inventaris = Inventaris::findOrFail($request->inventaris_id);

        $validate = Validator::make($request->all(), [
            'borrow_by'     => 'required',
            'inventaris_id' => 'required|exists:inventaris,id',
            'quantity'      => 'nullable|integer|min:1',
            'date_borrow'   => 'required|date',
            'date_back'     => 'nullable|date|after_or_equal:date_borrow',
            'status'        => 'required',
            'img_borrow'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $requestedQuantity = $request->quantity ?? 1;
        $quantityDifference = $requestedQuantity - $data->quantity;

        // Cek ketersediaan jumlah barang jika ada perubahan jumlah
        if ($quantityDifference > 0 && $quantityDifference > $inventaris->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Jumlah yang diminta melebihi stok yang tersedia'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Hapus gambar lama jika ada gambar baru
            if ($request->hasFile('img_borrow')) {
                if ($data->img_borrow) {
                    $this->supabase->deleteFile($data->img_borrow);
                }

                // Upload gambar baru dengan nama yang di-hash
                $file = $request->file('img_borrow');
                $extension = $file->getClientOriginalExtension();
                $hashedName = md5($file->getClientOriginalName() . time()) . '.' . $extension;
                $filePath = 'borrow_images/' . $hashedName;
                $this->supabase->uploadFile($file, $filePath);
                $data->img_borrow = $filePath;
            }

            // Logika untuk mengembalikan stok ketika status diubah menjadi Dikembalikan
            if ($data->status == 'Dipinjam' && $request->status == 'Dikembalikan') {
                // Kembalikan stok ke inventaris
                $inventaris->quantity += $data->quantity;
                $inventaris->save();
            } 
            // Logika untuk mengurangi stok ketika status diubah dari Dikembalikan menjadi Dipinjam
            else if ($data->status == 'Dikembalikan' && $request->status == 'Dipinjam') {
                // Kurangi stok dari inventaris
                if ($inventaris->quantity < $requestedQuantity) {
                    throw new \Exception('Stok tidak mencukupi untuk dipinjam kembali');
                }
                $inventaris->quantity -= $requestedQuantity;
                $inventaris->save();
            }
            // Logika untuk perubahan jumlah pinjaman
            else if ($request->status == 'Dipinjam') {
                // Jika status masih Dipinjam, hitung selisih jumlah
                $inventaris->quantity -= $quantityDifference;
                $inventaris->save();
            }

            $data->update([
                'borrow_by'     => $request->borrow_by,
                'inventaris_id' => $request->inventaris_id,
                'quantity'      => $requestedQuantity,
                'date_borrow'   => $request->date_borrow,
                'date_back'     => $request->date_back,
                'status'        => $request->status,
                'img_borrow'    => $data->img_borrow
            ]);

            DB::commit();
            return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $data = Borrow::findOrFail($id);
        $inventaris = Inventaris::findOrFail($data->inventaris_id);

        DB::beginTransaction();
        try {
            // Hapus gambar jika ada
            if ($data->img_borrow) {
                $this->supabase->deleteFile($data->img_borrow);
            }

            // Kembalikan jumlah barang
            $inventaris->quantity += $data->quantity;
            $inventaris->save();

            $data->delete();
            
            DB::commit();
            return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }
}
