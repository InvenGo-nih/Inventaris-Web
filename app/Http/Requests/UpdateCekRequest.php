<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition' => ['required', Rule::in(['Normal', 'Rusak'])],
            'broken_description' => ['required_if:condition,Rusak', 'nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:'.config('inventaris.image_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'condition.required' => 'Kondisi harus diisi.',
            'condition.in' => 'Kondisi tidak valid.',
            'broken_description.required_if' => 'Rincian rusak wajib diisi saat kondisi Rusak.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, jpg, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 10MB.',
            'image.uploaded' => 'Gambar gagal diunggah. Ukuran file melebihi batas upload server (maksimal 10MB).',
        ];
    }
}
