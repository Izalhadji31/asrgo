<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'sopir_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'driver')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sopir_id.required' => 'Pilih sopir terlebih dahulu.',
            'sopir_id.exists' => 'Sopir tidak ditemukan atau bukan akun sopir.',
        ];
    }
}
