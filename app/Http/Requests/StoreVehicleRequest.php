<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'mitra';
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'plat_nomor' => ['required', 'string', 'max:50'],
            'jenis' => ['required', 'string', 'max:50'],
            'kapasitas_penumpang' => ['nullable', 'integer', 'min:1', 'max:100'],
            'prioritas_travel' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'in:tersedia,disewa,maintenance'],
            'harga_sewa_per_hari' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
