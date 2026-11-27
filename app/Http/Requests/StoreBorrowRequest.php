<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBorrowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'borrow_by' => ['required', 'string', 'max:255'],
            'inventaris_id' => ['required', 'integer', 'exists:inventaris,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'date_borrow' => ['required', 'date'],
            'date_back' => ['nullable', 'date', 'after_or_equal:date_borrow'],
            'max_return_date' => ['required', 'date', 'after_or_equal:date_borrow'],
            'status' => ['required', Rule::in(['Dipinjam', 'Dikembalikan'])],
            'img_borrow' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:'.config('inventaris.image_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'borrow_by.required' => 'Nama peminjam harus diisi',
            'inventaris_id.required' => 'Barang harus diisi',
            'inventaris_id.exists' => 'Barang tersebut tidak ada',
            'date_borrow.required' => 'Tanggal peminjaman harus diisi',
            'date_borrow.date' => 'Tanggal peminjaman harus berupa tanggal',
            'date_back.date' => 'Tanggal kembali harus berupa tanggal',
            'date_back.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam',
            'max_return_date.required' => 'Batas waktu pengembalian harus diisi',
            'max_return_date.date' => 'Batas waktu pengembalian harus berupa tanggal',
            'max_return_date.after_or_equal' => 'Batas waktu pengembalian tidak boleh sebelum tanggal pinjam',
            'status.required' => 'Status peminjaman harus diisi',
            'status.in' => 'Status peminjaman tidak valid',
            'img_borrow.required' => 'Gambar peminjam harus diisi',
            'img_borrow.image' => 'Gambar peminjam harus berupa gambar',
            'img_borrow.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
            'img_borrow.max' => 'Ukuran gambar maksimal 10MB',
            'img_borrow.uploaded' => 'Gambar gagal diunggah. Ukuran file melebihi batas upload server (maksimal 10MB).',
        ];
    }
}
