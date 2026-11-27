<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupInventarisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $imageRule = $this->isMethod('post') ? 'required' : 'nullable';

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'image' => [$imageRule, 'image', 'mimes:jpeg,png,jpg,webp', 'max:'.config('inventaris.image_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama barang harus diisi.',
            'image.required' => 'Gambar harus diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, jpg, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 10MB.',
            'image.uploaded' => 'Gambar gagal diunggah. Ukuran file melebihi batas upload server (maksimal 10MB).',
            'type.required' => 'Tipe barang harus diisi.',
        ];
    }
}
