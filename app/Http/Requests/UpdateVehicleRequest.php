<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $vehicle = $this->route('vehicle');

        return $user?->role === 'admin' || $vehicle?->mitra_id === $user?->id;
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
            'harga_sewa_tanpa_sopir_per_hari' => ['required', 'integer', 'min:0'],
            'harga_sewa_dengan_sopir_per_hari' => ['required', 'integer', 'min:0'],
            'tarif_sopir_harian' => ['nullable', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
