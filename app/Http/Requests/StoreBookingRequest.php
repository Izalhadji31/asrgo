<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'vehicle_id'      => [
                'required_if:service_type,rental',
                'nullable',
                Rule::exists('vehicles', 'id')->where(fn ($query) => $query
                    ->where('is_approved', true)
                    ->where('status', 'tersedia')),
            ],
            'route_id'        => [
                'required_if:service_type,travel',
                'nullable',
                Rule::exists('routes', 'id')->where(fn ($query) => $query->where('service_type', 'travel')),
            ],
            'service_type'    => ['required', Rule::in(['rental', 'travel'])],
            'tanggal_mulai'   => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'jumlah_penumpang' => ['required_if:service_type,travel', 'nullable', 'integer', 'min:1', 'max:100'],
            'origin'          => ['nullable', 'string', 'max:255'],
            'destination'     => ['nullable', 'string', 'max:255'],
            'flight_number'   => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'session'         => ['required_if:service_type,travel', 'in:pagi,siang'],
            'with_driver'     => ['nullable', 'boolean'],
            'duration'        => ['required_if:service_type,rental', 'nullable', 'numeric', 'in:0.5,1,2,3,4,5,6,7'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required'                => 'Pilih kendaraan terlebih dahulu.',
            'vehicle_id.exists'                  => 'Kendaraan tidak ditemukan.',
            'service_type.required'              => 'Pilih jenis layanan.',
            'service_type.in'                    => 'Jenis layanan tidak valid.',
            'route_id.required_if'               => 'Pilih rute travel terlebih dahulu.',
            'tanggal_mulai.required'             => 'Tanggal mulai harus diisi.',
            'tanggal_selesai.after_or_equal'     => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'jumlah_penumpang.required_if'       => 'Jumlah penumpang harus diisi untuk pemesanan travel.',
            'jumlah_penumpang.min'               => 'Jumlah penumpang minimal 1 orang.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('service_type') !== 'travel') {
                return;
            }

            $route = Route::where('service_type', 'travel')
                ->whereHas('assignments', fn ($query) => $query->whereNotNull('mitra_id'))
                ->find($this->input('route_id'));

            if (!$route) {
                $validator->errors()->add('route_id', 'Rute travel belum memiliki mitra yang aktif.');
            } elseif (($this->filled('origin') || $this->filled('destination')) && (
                !$this->filled('origin')
                || !$this->filled('destination')
                || strcasecmp(trim((string) $this->input('origin')), trim($route->origin)) !== 0
                || strcasecmp(trim((string) $this->input('destination')), trim($route->destination)) !== 0
            )) {
                $validator->errors()->add('route_id', 'Kota asal dan tujuan harus mengikuti kota yang tersedia pada rute.');
            }

            if (!$this->filled('tanggal_mulai')) {
                return;
            }

            try {
                $departureDate = Carbon::createFromFormat('Y-m-d', $this->input('tanggal_mulai'))->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if ($departureDate->isBefore(today())) {
                $validator->errors()->add('tanggal_mulai', 'Tanggal keberangkatan travel sudah lewat. Pilih tanggal hari ini atau setelahnya.');

                return;
            }

            if (!$departureDate->isToday() || !in_array($this->input('session'), ['pagi', 'siang'], true)) {
                return;
            }

            $departureTime = $this->input('session') === 'pagi' ? [8, 0] : [12, 0];
            $departureAt = $departureDate->setTime($departureTime[0], $departureTime[1]);

            if (now()->greaterThanOrEqualTo($departureAt)) {
                $label = $this->input('session') === 'pagi' ? 'Pagi (08:00)' : 'Siang (12:00)';
                $validator->errors()->add('session', "Jam keberangkatan {$label} sudah lewat. Pilih sesi atau tanggal lain.");
            }
        });
    }
}
