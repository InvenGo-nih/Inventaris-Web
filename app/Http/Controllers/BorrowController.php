<?php

namespace App\Http\Controllers;

use App\DataTables\BorrowDataTable;
use App\Http\Requests\StoreBorrowRequest;
use App\Http\Requests\UpdateBorrowRequest;
use App\Models\Borrow;
use App\Models\Inventaris;
use App\Services\InventoryContextService;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BorrowController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index(BorrowDataTable $dataTable)
    {
        return $dataTable->render('borrow.index');
    }

    public function form($id = null)
    {
        $data = $id ? Borrow::findOrFail($id) : new Borrow;

        if ($id) {
            $inventaris = Inventaris::where(function ($query) use ($data) {
                $query->where('is_borrow', 0);

                if ($data->inventaris_id) {
                    $query->orWhere('id', $data->inventaris_id);
                }
            })->get();
        } else {
            $inventaris = Inventaris::where('is_borrow', 0)->get();
        }

        return view('borrow.form', compact('data', 'inventaris'));
    }

    public function store(StoreBorrowRequest $request)
    {
        $validated = $request->validated();
        $quantity = (int) ($validated['quantity'] ?? 1);
        $file = $request->file('img_borrow');
        $filePath = 'borrow_images/'.Str::uuid().'.'.$file->extension();

        try {
            $this->supabase->uploadFile($file, $filePath);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar peminjaman.');
        }

        try {
            DB::transaction(function () use ($validated, $quantity, $filePath) {
                $item = Inventaris::withTrashed()
                    ->whereKey($validated['inventaris_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($item->trashed()) {
                    throw ValidationException::withMessages([
                        'inventaris_id' => 'Barang sudah dihapus dan tidak dapat dipinjam.',
                    ]);
                }

                if ($validated['status'] === 'Dipinjam') {
                    $available = (int) ($item->quantity ?? 1);

                    if ($item->is_borrow || $available < $quantity) {
                        throw ValidationException::withMessages([
                            'inventaris_id' => 'Barang tidak tersedia atau stok tidak mencukupi.',
                        ]);
                    }

                    $item->decrement('quantity', $quantity);
                    $item->is_borrow = true;
                    $item->save();
                }

                $dateBack = $validated['date_back'] ?? null;

                if ($validated['status'] === 'Dikembalikan' && ! $dateBack) {
                    $dateBack = now()->toDateString();
                }

                Borrow::create([
                    'borrow_by' => $validated['borrow_by'],
                    'inventaris_id' => $item->id,
                    'quantity' => $quantity,
                    'date_borrow' => $validated['date_borrow'],
                    'date_back' => $dateBack,
                    'max_return_date' => $validated['max_return_date'],
                    'status' => $validated['status'],
                    'img_borrow' => $filePath,
                ]);
            });
        } catch (ValidationException $e) {
            $this->supabase->deleteFile($filePath);
            throw $e;
        } catch (\Throwable $e) {
            $this->supabase->deleteFile($filePath);
            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }

        $this->forgetDashboardCache();

        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil disimpan');
    }

    public function update(UpdateBorrowRequest $request, $id)
    {
        $validated = $request->validated();
        $data = Borrow::findOrFail($id);
        $oldImage = $data->img_borrow;
        $newImage = null;

        if ($request->hasFile('img_borrow')) {
            $file = $request->file('img_borrow');
            $newImage = 'borrow_images/'.Str::uuid().'.'.$file->extension();

            try {
                $this->supabase->uploadFile($file, $newImage);
            } catch (\Throwable $e) {
                report($e);

                return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar peminjaman.');
            }
        }

        try {
            DB::transaction(function () use ($id, $validated, $newImage) {
                $borrow = Borrow::whereKey($id)->lockForUpdate()->firstOrFail();
                $item = Inventaris::withTrashed()
                    ->whereKey($borrow->inventaris_id)
                    ->lockForUpdate()
                    ->first();

                $oldStatus = $borrow->status;
                $newStatus = $validated['status'];

                if ($oldStatus === 'Dipinjam' && $newStatus === 'Dikembalikan') {
                    if ($item) {
                        $item->quantity = (int) ($item->quantity ?? 0) + (int) $borrow->quantity;
                        $item->is_borrow = false;
                        $item->save();
                    }
                } elseif ($oldStatus === 'Dikembalikan' && $newStatus === 'Dipinjam') {
                    if (! $item || $item->trashed()) {
                        throw ValidationException::withMessages([
                            'status' => 'Barang sudah dihapus dan tidak dapat dipinjam kembali.',
                        ]);
                    }

                    $available = (int) ($item->quantity ?? 0);

                    if ($item->is_borrow || $available < (int) $borrow->quantity) {
                        throw ValidationException::withMessages([
                            'status' => 'Barang sudah dipinjam pihak lain atau stok tidak mencukupi.',
                        ]);
                    }

                    $item->decrement('quantity', (int) $borrow->quantity);
                    $item->is_borrow = true;
                    $item->save();
                }

                $dateBack = $validated['date_back'] ?? null;

                if ($newStatus === 'Dikembalikan' && ! $dateBack) {
                    $dateBack = now()->toDateString();
                }

                $borrow->fill([
                    'borrow_by' => $validated['borrow_by'],
                    'date_borrow' => $validated['date_borrow'],
                    'date_back' => $dateBack,
                    'max_return_date' => $validated['max_return_date'],
                    'status' => $newStatus,
                ]);

                if ($newImage) {
                    $borrow->img_borrow = $newImage;
                }

                $borrow->save();
            });
        } catch (ValidationException $e) {
            if ($newImage) {
                $this->supabase->deleteFile($newImage);
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($newImage) {
                $this->supabase->deleteFile($newImage);
            }

            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }

        if ($newImage && $oldImage) {
            $this->supabase->deleteFile($oldImage);
        }

        $this->forgetDashboardCache();

        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = Borrow::findOrFail($id);

        if ($data->status === 'Dipinjam') {
            return redirect()->back()->with('error', 'Selesaikan peminjaman (ubah status menjadi Dikembalikan) sebelum menghapus data.');
        }

        DB::transaction(function () use ($id) {
            $borrow = Borrow::whereKey($id)->lockForUpdate()->firstOrFail();
            $borrow->delete();
        });

        if ($data->img_borrow) {
            $this->supabase->deleteFile($data->img_borrow);
        }

        $this->forgetDashboardCache();

        return redirect()->route('borrow.index')->with('success', 'Data peminjaman berhasil dihapus');
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('dashboard:chart');
        Cache::forget('borrows:overdue_count');
        InventoryContextService::forgetFor(auth()->user());
    }
}
