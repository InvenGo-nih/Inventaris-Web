<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBorrowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'borrow_by' => ['required', 'string', 'max:255'],
            'inventaris_id' => ['sometimes', 'integer', 'exists:inventaris,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'date_borrow' => ['required', 'date'],
            'date_back' => ['nullable', 'date', 'after_or_equal:date_borrow'],
            'max_return_date' => ['required', 'date', 'after_or_equal:date_borrow'],
            'status' => ['required', Rule::in(['Dipinjam', 'Dikembalikan'])],
            'img_borrow' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:'.config('inventaris.image_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return (new StoreBorrowRequest)->messages();
    }
}
