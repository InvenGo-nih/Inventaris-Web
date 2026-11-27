<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventarisRequest;
use App\Http\Requests\UpdateInventarisRequest;
use App\Models\GroupInventaris;
use App\Models\Inventaris;
use App\Models\InventarisLocation;
use App\Services\InventoryContextService;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use TCPDF;

class InventarisController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index(Request $request, $id)
    {
        $title = GroupInventaris::findOrFail($id)->name;

        $stats = Cache::remember("inventaris:stats:{$id}", now()->addMinute(), function () use ($id) {
            return Inventaris::where('group_inventaris_id', $id)
                ->selectRaw("COUNT(*) AS total, SUM(CASE WHEN condition = 'Rusak' THEN 1 ELSE 0 END) AS rusak, SUM(CASE WHEN condition = 'Normal' THEN 1 ELSE 0 END) AS normal")
                ->first();
        });

        $jumlah = (int) ($stats->total ?? 0);
        $jumlahRusak = (int) ($stats->rusak ?? 0);
        $jumlahNormal = (int) ($stats->normal ?? 0);

        $query = Inventaris::where('group_inventaris_id', $id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('condition', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate(config('inventaris.items_per_page', 10));

        return view('inventaris.index', compact('data', 'jumlah', 'title', 'jumlahRusak', 'jumlahNormal'));
    }

    public function form($group_inventaris_id, $id = null)
    {
        $data = $id ? Inventaris::findOrFail($id) : new Inventaris;
        $location = InventarisLocation::all();

        return view('inventaris.form', compact('data', 'location', 'group_inventaris_id'));
    }

    public function store(StoreInventarisRequest $request)
    {
        $validated = $request->validated();
        $file = $request->file('image');
        $filePath = 'inventaris_images/'.Str::uuid().'.'.$file->extension();

        try {
            $this->supabase->uploadFile($file, $filePath);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar inventaris.');
        }

        try {
            $data = Inventaris::create([
                'group_inventaris_id' => $validated['group_inventaris_id'],
                'name' => $validated['name'],
                'image' => $filePath,
                'specification' => $validated['specification'],
                'condition' => $validated['condition'],
                'location' => $validated['location'],
                'serial_number' => $validated['serial_number'] ?? null,
                'broken_description' => $validated['broken_description'] ?? null,
                'type' => $validated['type'] ?? null,
                'quantity' => $validated['quantity'] ?? 1,
            ]);

            $data->qr_link = route('test_qr', ['id' => $data->id]);
            $data->save();
        } catch (\Throwable $e) {
            $this->supabase->deleteFile($filePath);
            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan inventaris.');
        }

        Cache::forget("inventaris:stats:{$data->group_inventaris_id}");
        Cache::forget('dashboard:chart');
        InventoryContextService::forgetFor($request->user());

        return redirect()->route('inventaris.inventaris', $data->group_inventaris_id)->with('success', 'Inventaris berhasil disimpan');
    }

    public function update(UpdateInventarisRequest $request, $id)
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

        $data->fill([
            'name' => $validated['name'],
            'specification' => $validated['specification'],
            'condition' => $validated['condition'],
            'location' => $validated['location'],
            'serial_number' => $validated['serial_number'] ?? null,
            'broken_description' => $validated['broken_description'] ?? null,
        ]);

        if ($newImage) {
            $data->image = $newImage;
        }

        $data->qr_link = route('test_qr', ['id' => $data->id]);

        try {
            $data->save();
        } catch (\Throwable $e) {
            if ($newImage) {
                $this->supabase->deleteFile($newImage);
            }

            report($e);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui inventaris.');
        }

        if ($newImage && $oldImage) {
            $this->supabase->deleteFile($oldImage);
        }

        Cache::forget("inventaris:stats:{$data->group_inventaris_id}");
        Cache::forget('dashboard:chart');
        InventoryContextService::forgetFor($request->user());

        return redirect()->route('inventaris.inventaris', $data->group_inventaris_id)->with('success', 'Inventaris berhasil diperbarui');
    }

    public function show($id)
    {
        $data = Inventaris::findOrFail($id);

        return view('inventaris.show', compact('data'));
    }

    public function showQr($id)
    {
        $data = Inventaris::findOrFail($id);

        return view('inventaris.showQrData', compact('data'));
    }

    public function destroy($id)
    {
        $data = Inventaris::findOrFail($id);
        $group = $data->group_inventaris_id;

        if ($data->is_borrow || $data->borrow()->where('status', 'Dipinjam')->exists()) {
            return redirect()->route('inventaris.inventaris', $group)->with('error', 'Inventaris tidak dapat dihapus karena masih dalam peminjaman');
        }

        try {
            $image = $data->image;
            $data->delete();

            if ($image) {
                $this->supabase->deleteFile($image);
            }

            Cache::forget("inventaris:stats:{$group}");
            Cache::forget('dashboard:chart');
            InventoryContextService::forgetFor(auth()->user());

            return redirect()->route('inventaris.inventaris', $group)->with('success', 'Inventaris berhasil dihapus');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('inventaris.inventaris', $group)->with('error', 'Terjadi kesalahan saat menghapus inventaris');
        }
    }

    public function downloadPDF($group_inventaris_id)
    {
        $group = GroupInventaris::findOrFail($group_inventaris_id);
        $inventaris = Inventaris::where('group_inventaris_id', $group_inventaris_id)->cursor();

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor((string) config('app.name'));
        $pdf->SetTitle('Laporan Inventaris - '.$group->name);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Laporan Inventaris - '.$group->name, 0, 1, 'C');
        $pdf->Ln(5);

        $columnWidths = [50, 50, 50, 40];
        $lineHeight = (int) config('inventaris.pdf.line_height', 30);
        $rowsPerPage = max(1, (int) config('inventaris.pdf.rows_per_page', 7));

        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($columnWidths[0], $lineHeight, 'Nama Barang', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[1], $lineHeight, 'Kondisi', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[2], $lineHeight, 'Spesifikasi', 1, 0, 'C', true);
        $pdf->Cell($columnWidths[3], $lineHeight, 'QR Code', 1, 1, 'C', true);

        foreach ($inventaris as $index => $item) {
            if (($index > 0) && ($index % $rowsPerPage == 0)) {
                $pdf->AddPage();
                $pdf->SetFillColor(200, 200, 200);
                $pdf->Cell($columnWidths[0], $lineHeight, 'Nama Barang', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[1], $lineHeight, 'Kondisi', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[2], $lineHeight, 'Spesifikasi', 1, 0, 'C', true);
                $pdf->Cell($columnWidths[3], $lineHeight, 'QR Code', 1, 1, 'C', true);
            }

            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell($columnWidths[0], $lineHeight, (string) $item->name, 1, 0, 'L');
            $pdf->Cell($columnWidths[1], $lineHeight, (string) $item->condition, 1, 0, 'C');
            $pdf->Cell($columnWidths[2], $lineHeight, (string) $item->specification, 1, 0, 'L');

            $xPos = $pdf->GetX();
            $yPos = $pdf->GetY();

            $pdf->Cell($columnWidths[3], $lineHeight, '', 1, 1, 'C');

            $qrSize = (int) config('inventaris.pdf.qr_size', 15);
            $qrX = $xPos + ($columnWidths[3] - $qrSize) / 2;
            $qrY = $yPos + ($lineHeight - $qrSize) / 2;
            $qrLink = $item->qr_link ?: route('test_qr', ['id' => $item->id]);
            $pdf->write2DBarcode($qrLink, 'QRCODE,M', $qrX, $qrY, $qrSize, $qrSize);
        }

        return response($pdf->Output('inventaris.pdf', 'D'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
