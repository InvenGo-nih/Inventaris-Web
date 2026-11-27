<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventarisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_inventaris_id' => ['required', 'integer', 'exists:group_inventaris,id'],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:inventaris,serial_number'],
            'condition' => ['required', Rule::in(['Normal', 'Rusak'])],
            'specification' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'broken_description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:'.config('inventaris.image_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama barang harus diisi',
            'serial_number.unique' => 'Serial number sudah digunakan',
            'condition.required' => 'Kondisi barang harus diisi',
            'condition.in' => 'Kondisi barang tidak valid',
            'specification.required' => 'Spesifikasi barang harus diisi',
            'location.required' => 'Lokasi barang harus diisi',
            'image.required' => 'Gambar barang harus diisi',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
            'image.max' => 'Ukuran gambar maksimal 10MB',
            'image.uploaded' => 'Gambar gagal diunggah. Ukuran file melebihi batas upload server (maksimal 10MB).',
        ];
    }
}
