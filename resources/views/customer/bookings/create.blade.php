@extends('layouts.customer')

@section('content')
    <script>
        window.__bookingData = {
            initialVehicleId: @json(old('vehicle_id') ? (int) old('vehicle_id') : null),
            initialServiceType: @json(old('service_type', 'rental')),
            initialRouteId: @json(old('route_id') ? (int) old('route_id') : null),
            initialRouteOrigin: @json(old('origin')),
            initialRouteDestination: @json(old('destination')),
            initialSession: @json(old('session')),
            initialDuration: @json(old('duration')),
            initialTravelDate: @json(old('tanggal_mulai')),
            initialPassengerCount: @json(old('jumlah_penumpang', 1)),
            initialPassengers: @json(old('passengers')),
            userName: @json(Auth::user()->name),
            initialWithDriver: @json(old('with_driver', '1')),
            routeAssignments: @json($routeAssignments),
            routesData: @json($routesData),
            vehiclesData: @json($vehiclesData),
            vehicleReservations: @json($vehicleReservations),
            travelBookingCounts: @json($travelBookingCounts),
            travelDepartures: @json($travelDepartures),
        };
    </script>
    <div class="space-y-6" x-data="{
         serviceType: window.__bookingData.initialServiceType,
         withDriver: window.__bookingData.initialWithDriver,
         selectedVehicleHasSopir: false,
         formErrors: [],
         selectedRouteId: window.__bookingData.initialRouteId,
         selectedRouteOrigin: window.__bookingData.initialRouteOrigin,
         selectedRouteDestination: window.__bookingData.initialRouteDestination,
        selectedRoutePrice: 0,
        selectedRouteSession: window.__bookingData.initialSession,
        selectedRouteVehicle: null,
        selectedRouteDriver: null,
        selectedVehicleId: window.__bookingData.initialVehicleId,
        selectedVehicleName: '',
        selectedVehiclePlat: '',
        selectedVehicleFoto: '',
        selectedVehicleJenis: '',
         selectedVehicleHarga: 0,
         selectedVehicleHargaTanpaSopir: 0,
         selectedVehicleHargaDenganSopir: 0,
         selectedDuration: window.__bookingData.initialDuration,
         passengerCount: Number(window.__bookingData.initialPassengerCount),
         passengers: window.__bookingData.initialPassengers && window.__bookingData.initialPassengers.length
             ? window.__bookingData.initialPassengers.map(p => ({ nama: p.nama || '', no_hp: p.no_hp || '' }))
             : [{ nama: window.__bookingData.userName || '', no_hp: '' }],
          travelDate: window.__bookingData.initialTravelDate,
         get routeOrigins() {
             return [...new Set(window.__bookingData.routesData.map(route => route.origin))].sort();
         },
         get routeDestinations() {
             if (!this.selectedRouteOrigin) return [];
             return [...new Set(window.__bookingData.routesData
                 .filter(route => route.origin === this.selectedRouteOrigin)
                 .map(route => route.destination))].sort();
         },
         get matchingRoutes() {
             return window.__bookingData.routesData.filter(route =>
                 route.origin === this.selectedRouteOrigin && route.destination === this.selectedRouteDestination
             );
         },
         get todayDate() {
             const now = new Date();
             return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
         },
         get minimumTravelDate() {
             const now = new Date();
             const minutes = now.getHours() * 60 + now.getMinutes();
             const date = new Date(now.getFullYear(), now.getMonth(), now.getDate());

             // No travel session starts after 12:00, so today is no longer bookable.
             if (minutes >= 12 * 60) date.setDate(date.getDate() + 1);

             return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
         },
        get rentalPricePerDay() {
            if (this.withDriver === '1') {
                return this.selectedVehicleHargaDenganSopir || this.selectedVehicleHarga;
            }
            return this.selectedVehicleHargaTanpaSopir || this.selectedVehicleHarga;
        },
        get rentalTotal() {
            if (!this.rentalPricePerDay || !this.selectedDuration) return 0;
            let dur = parseFloat(this.selectedDuration);
            if (dur === 0.5) return Math.ceil(this.rentalPricePerDay / 2);
            return this.rentalPricePerDay * dur;
        },
         get rentalTotalFormatted() {
             return 'Rp ' + new Intl.NumberFormat('id-ID').format(this.rentalTotal);
         },
         selectServiceType(type) {
             this.serviceType = type;
             this.selectedRouteId = null;
             this.selectedRoutePrice = 0;
             this.selectedRouteSession = null;
             this.selectedRouteVehicle = null;
             this.selectedRouteDriver = null;
             this.selectedVehicleId = null;
             this.selectedVehicleName = '';
             this.selectedVehiclePlat = '';
             this.selectedVehicleFoto = '';
             this.selectedVehicleJenis = '';
             this.selectedVehicleHarga = 0;
             this.selectedVehicleHargaTanpaSopir = 0;
             this.selectedVehicleHargaDenganSopir = 0;
             this.selectedDuration = null;

             if (type === 'travel' && (!this.travelDate || this.travelDate < this.minimumTravelDate)) {
                 this.travelDate = this.minimumTravelDate;
             }
         },
        categories: [
            { key: 'rental', label: 'Rental Mobil', icon: 'fa-car' },
            { key: 'travel', label: 'Travel', icon: 'fa-bus' },
        ],
         init() {
             if (this.serviceType === 'travel' && this.travelDate && this.travelDate < this.minimumTravelDate) {
                 this.travelDate = this.minimumTravelDate;
             }
             if (this.selectedRouteId) {
                 const route = window.__bookingData.routesData.find(r => r.id == this.selectedRouteId);
                 if (route) {
                     this.applyRoute(route);
                     this.selectedRouteSession = window.__bookingData.initialSession;
                 }
             }
             if (this.matchingRoutes.length === 1) this.applyRoute(this.matchingRoutes[0]);
             if (this.selectedRouteId && this.selectedRouteSession && this.isSessionAvailable(this.selectedRouteSession)) {
                  this.selectSession(this.selectedRouteSession);
              } else if (this.selectedRouteSession) {
                  this.selectedRouteSession = null;
              }
             const initialVehicle = window.__bookingData.vehiclesData.find(vehicle => vehicle.id == this.selectedVehicleId);
             if (initialVehicle) {
                 this.selectedVehicleName = initialVehicle.nama;
                 this.selectedVehiclePlat = initialVehicle.plat_nomor;
                 this.selectedVehicleFoto = initialVehicle.foto;
                 this.selectedVehicleJenis = initialVehicle.jenis;
                 this.selectedVehicleHarga = initialVehicle.harga;
                 this.selectedVehicleHargaTanpaSopir = Number(initialVehicle.harga_tanpa_sopir || 0);
                 this.selectedVehicleHargaDenganSopir = Number(initialVehicle.harga_dengan_sopir || 0);
                 this.selectedVehicleHasSopir = Boolean(initialVehicle.has_sopir);
             }
         },
         isSessionTimeAvailable(session) {
             if (this.serviceType !== 'travel' || !this.travelDate) return true;

             const today = this.todayDate;
             if (this.travelDate > today) return true;
             if (this.travelDate < today) return false;

             const now = new Date();
             const currentMinutes = now.getHours() * 60 + now.getMinutes();
             const startMinutes = session === 'pagi' ? 8 * 60 : 12 * 60;

             return currentMinutes < startMinutes;
         },
         bookingCountFor(assignment) {
             const key = [assignment.id, assignment.session, assignment.vehicle.id, this.travelDate].join('|');
             return Number(window.__bookingData.travelBookingCounts[key] || 0);
         },
         isVehicleAvailable(assignment) {
             if (!assignment.vehicle || !assignment.driver || !this.isSessionTimeAvailable(assignment.session)) return false;
             if (!this.travelDate) return true;

             const departureKey = [assignment.assignment_id, assignment.vehicle.id, this.travelDate].join('|');
             if (window.__bookingData.travelDepartures.includes(departureKey)) return false;

             return this.bookingCountFor(assignment) + Number(this.passengerCount || 1) <= Number(assignment.vehicle.kapasitas_penumpang || 0);
         },
         isSessionAvailable(session) {
             return window.__bookingData.routeAssignments.some(assignment =>
                 assignment.id == this.selectedRouteId && assignment.session === session && this.isVehicleAvailable(assignment)
             );
         },
         get availableSessions() {
             if (!this.selectedRouteId) return [];
             const assignments = window.__bookingData.routeAssignments;
             return assignments
                 .filter(a => a.id == this.selectedRouteId && this.isVehicleAvailable(a))
                 .map(a => a.session)
                 .filter((session, index, sessions) => sessions.indexOf(session) === index)
                 .filter(session => this.isSessionAvailable(session));
         },
         get sessionOptions() {
             if (!this.selectedRouteId) return [];

             return window.__bookingData.routeAssignments
                 .filter(a => a.id == this.selectedRouteId && a.vehicle && a.driver)
                 .map(a => a.session)
                 .filter((session, index, sessions) => sessions.indexOf(session) === index);
         },
         selectRoute(el) {
             const route = window.__bookingData.routesData.find(r => r.id == el.value);
             if (route) this.applyRoute(route);
             else this.resetRoute();
         },
         applyRoute(route) {
             this.selectedRouteId = route.id;
             this.selectedRoutePrice = Number(route.price || 0);
             this.selectedRouteOrigin = route.origin;
             this.selectedRouteDestination = route.destination;
             this.selectedRouteSession = null;
             this.selectedRouteVehicle = null;
             this.selectedRouteDriver = null;
         },
         resetRoute() {
             this.selectedRouteId = null;
             this.selectedRoutePrice = 0;
             this.selectedRouteSession = null;
             this.selectedRouteVehicle = null;
             this.selectedRouteDriver = null;
         },
         selectRouteOrigin(origin) {
             this.selectedRouteOrigin = origin;
             this.selectedRouteDestination = '';
             this.resetRoute();
         },
         selectRouteDestination(destination) {
             this.selectedRouteDestination = destination;
             this.resetRoute();
             if (this.matchingRoutes.length === 1) this.applyRoute(this.matchingRoutes[0]);
         },
         selectSession(session) {
             if (!this.isSessionAvailable(session)) {
                 this.selectedRouteSession = null;
                 return;
             }

             this.selectedRouteSession = session;
            const assignments = window.__bookingData.routeAssignments;
             const match = assignments.find(a => a.id == this.selectedRouteId && a.session == session && this.isVehicleAvailable(a));
            if (match) {
                this.selectedRouteVehicle = match.vehicle;
                this.selectedRouteDriver = match.driver;
            } else {
                this.selectedRouteVehicle = null;
                 this.selectedRouteDriver = null;
             }
         },
         updateTravelDate(date) {
             this.travelDate = date;

             if (this.serviceType === 'rental' && this.selectedVehicleId && !this.isRentalVehicleAvailable(this.selectedVehicleId)) {
                 this.selectVehicle({id: null, nama: '', plat_nomor: '', foto: '', jenis: '', harga: 0});
             }

             if (this.selectedRouteSession && !this.isSessionAvailable(this.selectedRouteSession)) {
                 this.selectedRouteSession = null;
                 this.selectedRouteVehicle = null;
                 this.selectedRouteDriver = null;
             }
         },
         updatePassengerCount(count) {
             this.passengerCount = Number(count || 1);

             const target = Math.max(1, this.passengerCount);
             if (target > this.passengers.length) {
                 for (let i = this.passengers.length; i < target; i++) {
                     this.passengers.push({ nama: '', no_hp: '' });
                 }
             } else if (target < this.passengers.length) {
                 this.passengers = this.passengers.slice(0, target);
             }

             if (this.selectedRouteSession && !this.isSessionAvailable(this.selectedRouteSession)) {
                 this.selectedRouteSession = null;
                 this.selectedRouteVehicle = null;
                 this.selectedRouteDriver = null;
             }
         },
         selectVehicle(vehicle) {
            this.selectedVehicleId = vehicle.id;
            window.__bookingData.selectedVehicleId = vehicle.id;
            this.selectedVehicleName = vehicle.nama;
            this.selectedVehiclePlat = vehicle.plat_nomor;
            this.selectedVehicleFoto = vehicle.foto;
            this.selectedVehicleJenis = vehicle.jenis;
            this.selectedVehicleHarga = vehicle.harga;
            this.selectedVehicleHargaTanpaSopir = Number(vehicle.harga_tanpa_sopir || 0);
            this.selectedVehicleHargaDenganSopir = Number(vehicle.harga_dengan_sopir || 0);
            this.selectedVehicleHasSopir = Boolean(vehicle.has_sopir);
            $dispatch('close-modal', 'vehicle-picker');
          },
         selectDuration(el) {
             this.selectedDuration = el.value;
             if (this.selectedVehicleId && !this.isRentalVehicleAvailable(this.selectedVehicleId)) {
                 this.selectVehicle({id: null, nama: '', plat_nomor: '', foto: '', jenis: '', harga: 0});
             }
         },
         get rentalEndDate() {
             if (!this.travelDate) return '';
             const days = Math.max(1, Math.ceil(Number(this.selectedDuration || 1)));
             const end = new Date(`${this.travelDate}T00:00:00`);
             end.setDate(end.getDate() + days - 1);
             return `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;
         },
         isRentalVehicleAvailable(vehicleId) {
             if (!this.travelDate) return true;
             const reservations = window.__bookingData.vehicleReservations[vehicleId] || [];
             return !reservations.some(reservation =>
                 reservation.start <= this.rentalEndDate && reservation.end >= this.travelDate
             );
         },
         validateBookingForm() {
             const missing = [];
             if (this.serviceType === 'rental' && !this.selectedVehicleId) missing.push('Pilih kendaraan.');
             if (this.serviceType === 'rental' && !this.selectedDuration) missing.push('Pilih durasi sewa.');
             if (this.serviceType === 'travel' && !this.selectedRouteId) missing.push('Pilih rute travel.');
             if (this.serviceType === 'travel' && !this.passengerCount) missing.push('Isi jumlah penumpang.');
             if (this.serviceType === 'travel' && !this.selectedRouteSession) missing.push('Pilih sesi keberangkatan.');
             if (!this.travelDate) missing.push('Pilih tanggal mulai.');
             this.formErrors = missing;
             return missing.length === 0;
         }
    }" x-on:pick-vehicle.window="selectVehicle($event.detail)">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Buat Booking Baru</h1>
            <p class="mt-1 text-sm text-slate-500">Pilih jenis layanan dan isi detail pemesanan Anda.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('bookings.store') }}" method="POST" novalidate x-on:submit="if (!validateBookingForm()) $event.preventDefault()">
                @csrf
                @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="font-semibold text-red-700">Booking gagal disimpan:</p>
                    <ul class="ml-5 list-disc text-sm text-red-600">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif
                <div x-show="formErrors.length" style="display:none" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="font-semibold text-red-700">Lengkapi data berikut sebelum menyimpan:</p>
                    <ul class="ml-5 list-disc text-sm text-red-600">
                        <template x-for="err in formErrors"><li x-text="err"></li></template>
                    </ul>
                </div>
                <input type="hidden" name="service_type" :value="serviceType">
                <input type="hidden" name="route_id" :value="selectedRouteId">
                <input type="hidden" name="vehicle_id" :value="selectedVehicleId">
                <input type="hidden" name="session" :value="selectedRouteSession">
                <input type="hidden" name="origin" :value="selectedRouteOrigin">
                <input type="hidden" name="destination" :value="selectedRouteDestination">

                <div class="mb-6 flex flex-wrap justify-center gap-3">
                    <template x-for="cat in categories" :key="cat.key">
                        <button type="button"
                             @click="selectServiceType(cat.key)"
                            :class="serviceType === cat.key
                                ? 'bg-blue-900 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                            class="flex items-center gap-2 rounded-lg px-5 py-3 text-sm font-semibold transition">
                            <i :class="'fas ' + cat.icon"></i>
                            <span x-text="cat.label"></span>
                        </button>
                    </template>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                     {{-- Travel Route --}}
                     <div x-show="serviceType === 'travel'" class="md:col-span-2">
                         <label class="mb-1.5 block text-sm font-medium text-slate-700">Pilih Daerah Travel</label>
                         @if ($routes->isNotEmpty())
                             <div class="grid gap-3 md:grid-cols-2">
                                 <select x-model="selectedRouteOrigin" @change="selectRouteOrigin($event.target.value)" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                                     <option value="">— Pilih kota asal —</option>
                                     <template x-for="origin in routeOrigins" :key="origin">
                                         <option :value="origin" x-text="origin"></option>
                                     </template>
                                 </select>
                                 <select x-model="selectedRouteDestination" @change="selectRouteDestination($event.target.value)" :disabled="!selectedRouteOrigin" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20 disabled:cursor-not-allowed disabled:bg-slate-100">
                                     <option value="">— Pilih kota tujuan —</option>
                                     <template x-for="destination in routeDestinations" :key="destination">
                                         <option :value="destination" x-text="destination"></option>
                                     </template>
                                 </select>
                             </div>
                             <template x-if="matchingRoutes.length > 1">
                                 <select @change="selectRoute($el)" class="mt-3 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                                     <option value="">— Pilih rute —</option>
                                     <template x-for="route in matchingRoutes" :key="route.id">
                                         <option :value="route.id" x-text="`${route.origin} → ${route.destination} — Rp ${new Intl.NumberFormat('id-ID').format(route.price)}`"></option>
                                     </template>
                                 </select>
                             </template>
                             <template x-if="matchingRoutes.length === 1">
                                 <p class="mt-2 text-xs text-emerald-700"><i class="fas fa-route mr-1"></i>Rute tersedia: <span x-text="selectedRouteOrigin + ' → ' + selectedRouteDestination"></span></p>
                             </template>
                         @else
                             <p class="text-sm text-amber-700 rounded-xl border border-amber-200 bg-amber-50 p-4">Belum ada rute travel. Hubungi admin.</p>
                         @endif
                        @error('route_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Travel Price --}}
                    <div x-show="serviceType === 'travel' && selectedRoutePrice > 0" class="md:col-span-2">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 text-center">
                            <span class="text-sm text-blue-700">Harga Rute: </span>
                            <span class="font-[IBM_Plex_Mono] text-lg font-bold text-blue-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedRoutePrice)"></span>
                        </div>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div x-show="serviceType === 'rental' || (serviceType === 'travel' && selectedRouteId)">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                         <input type="date" name="tanggal_mulai" x-model="travelDate" @change="updateTravelDate($event.target.value)" :min="serviceType === 'travel' ? minimumTravelDate : todayDate" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20" required>
                        @error('tanggal_mulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                     {{-- Passenger Count (Travel) --}}
                     <div x-show="serviceType === 'travel' && selectedRouteId">
                         <label class="mb-1.5 block text-sm font-medium text-slate-700">Jumlah Penumpang</label>
                         <div class="relative">
                              <input type="number" name="jumlah_penumpang" x-model.number="passengerCount" @input="updatePassengerCount($event.target.value)" min="1" max="100" x-bind:disabled="serviceType !== 'travel'" x-bind:required="serviceType === 'travel'" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 pr-20 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                             <span class="absolute inset-y-0 right-4 flex items-center text-sm text-slate-500">orang</span>
                         </div>
                         <p class="mt-1 text-xs text-slate-500">Kendaraan akan dipilih otomatis sesuai kapasitas dan antrean armada.</p>
                         @error('jumlah_penumpang') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                         </div>

                         {{-- Passenger Manifest (Travel) --}}
                         <div x-show="serviceType === 'travel' && selectedRouteId" class="md:col-span-2">
                         <label class="mb-1.5 block text-sm font-medium text-slate-700">Data Penumpang</label>
                         <p class="mb-2 text-xs text-slate-500">Penumpang pertama mengikuti akun Anda. Isi nama dan nomor HP setiap penumpang.</p>
                         <template x-for="(p, i) in passengers" :key="i">
                             <div class="mb-2 flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center">
                                 <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white" x-text="i + 1"></span>
                                 <input type="text" :name="`passengers[${i}][nama]`" x-model="passengers[i].nama" placeholder="Nama penumpang" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700" :required="serviceType === 'travel'">
                                 <input type="text" :name="`passengers[${i}][no_hp]`" x-model="passengers[i].no_hp" placeholder="No. HP (contoh: 081234567890)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700" :required="serviceType === 'travel'">
                             </div>
                         </template>
                         @error('passengers') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                         </div>

                         {{-- Available Sessions (Travel) --}}
                    <div x-show="serviceType === 'travel' && selectedRouteId" class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Pilih Jam Keberangkatan</label>
                         <template x-if="sessionOptions.length > 0">
                             <div class="flex gap-3">
                                 <template x-for="s in sessionOptions" :key="s">
                                     <button type="button" @click="selectSession(s)"
                                         :disabled="!isSessionAvailable(s)"
                                         :class="!isSessionAvailable(s)
                                             ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                                             : selectedRouteSession === s
                                            ? 'border-blue-900 bg-blue-50 text-blue-900'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-blue-400'"
                                        class="flex-1 rounded-xl border-2 px-4 py-4 text-center transition">
                                         <div class="text-lg font-semibold" x-text="s === 'pagi' ? 'Pagi' : 'Siang'"></div>
                                         <div class="mt-1 text-xs text-slate-500" x-text="s === 'pagi' ? '08:00 — 12:00' : '12:00 — 17:00'"></div>
                                         <div class="mt-2 text-xs font-semibold" :class="isSessionAvailable(s) ? 'text-emerald-600' : 'text-red-500'" x-text="isSessionAvailable(s) ? 'Tersedia' : 'Penuh / sudah berangkat'"></div>
                                     </button>
                                 </template>
                             </div>
                         </template>
                         <template x-if="sessionOptions.length === 0">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                 <p class="text-sm text-amber-700"><i class="fas fa-exclamation-triangle mr-2"></i>Tidak ada kendaraan yang masih tersedia untuk tanggal, jumlah penumpang, dan sesi ini. Pilih tanggal atau sesi lain.</p>
                            </div>
                        </template>
                        @error('session') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Travel Assigned Vehicle & Driver --}}
                    <div x-show="serviceType === 'travel' && selectedRouteVehicle" class="md:col-span-2">
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                             <p class="mb-3 text-sm font-semibold text-green-800">Kendaraan & Sopir Prioritas</p>
                            <div class="flex items-center gap-4">
                                <template x-if="selectedRouteVehicle?.foto">
                                    <img :src="selectedRouteVehicle.foto" :alt="selectedRouteVehicle.nama" class="h-16 w-24 rounded-lg object-cover">
                                </template>
                                <template x-if="!selectedRouteVehicle?.foto">
                                    <div class="flex h-16 w-24 items-center justify-center rounded-lg bg-slate-200 text-xs text-slate-400">No Foto</div>
                                </template>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800" x-text="selectedRouteVehicle?.nama"></p>
                                    <p class="font-[IBM_Plex_Mono] text-sm text-slate-600" x-text="selectedRouteVehicle?.plat_nomor"></p>
                                    <p class="text-xs text-slate-500 capitalize" x-text="selectedRouteVehicle?.jenis"></p>
                                    <p class="text-xs text-slate-500"><i class="fas fa-users mr-1"></i><span x-text="selectedRouteVehicle?.kapasitas_penumpang"></span> seater</p>
                                    <p class="mt-1 text-xs text-slate-500"><i class="fas fa-user-tie mr-1"></i><span x-text="selectedRouteDriver?.name || 'Sopir belum ditugaskan'"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vehicle Selector (Rental) --}}
                    <div x-show="serviceType === 'rental'" class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Pilih Kendaraan</label>

                        {{-- Selected Vehicle --}}
                        <div x-show="selectedVehicleId" x-transition class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <div class="flex items-center gap-4">
                                <template x-if="selectedVehicleFoto">
                                    <img :src="selectedVehicleFoto" :alt="selectedVehicleName" class="h-16 w-24 rounded-lg object-cover">
                                </template>
                                <template x-if="!selectedVehicleFoto">
                                    <div class="flex h-16 w-24 items-center justify-center rounded-lg bg-slate-200 text-xs text-slate-400">No Foto</div>
                                </template>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800" x-text="selectedVehicleName"></p>
                                    <p class="font-[IBM_Plex_Mono] text-sm text-slate-600" x-text="selectedVehiclePlat"></p>
                                    <p class="text-xs text-slate-500 capitalize" x-text="selectedVehicleJenis"></p>
                                    <p class="mt-1 font-[IBM_Plex_Mono] text-sm font-bold text-blue-900"
                                       x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(rentalPricePerDay) + ' /hari'"></p>
                                </div>
                                <button type="button" @click="selectVehicle({id: null, nama: '', plat_nomor: '', foto: '', jenis: '', harga: 0})"
                                    class="text-slate-400 hover:text-red-500">
                                    <i class="fas fa-times-circle text-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Select Button --}}
                        <button type="button" x-on:click="$dispatch('open-modal', 'vehicle-picker')"
                            class="w-full rounded-xl border-2 border-dashed border-slate-300 px-4 py-3.5 text-sm text-slate-500 transition hover:border-blue-400 hover:text-blue-600">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span x-text="selectedVehicleId ? 'Ganti Kendaraan' : 'Pilih Kendaraan'"></span>
                        </button>
                        @error('vehicle_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- With Driver (Rental) --}}
                    <div x-show="serviceType === 'rental'" class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Opsi Sopir</label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer rounded-xl border-2 px-4 py-3 text-center transition has-[:checked]:border-blue-900 has-[:checked]:bg-blue-50">
                                <input type="radio" name="with_driver" value="1" x-model="withDriver" @checked(old('with_driver', '1') === '1') class="sr-only" x-bind:disabled="serviceType !== 'rental'">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-user-tie text-blue-700"></i>
                                    <span class="text-sm font-semibold text-slate-700">Dengan Sopir</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">Kendaraan + sopir</p>
                            </label>
                            <label class="flex-1 cursor-pointer rounded-xl border-2 border-slate-200 px-4 py-3 text-center transition has-[:checked]:border-blue-900 has-[:checked]:bg-blue-50">
                                <input type="radio" name="with_driver" value="0" x-model="withDriver" @checked(old('with_driver') === '0') class="sr-only" x-bind:disabled="serviceType !== 'rental'">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-steering-wheel text-blue-700"></i>
                                    <span class="text-sm font-semibold text-slate-700">Tanpa Sopir (Lepas Kunci)</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">Hanya kendaraan</p>
                            </label>
                        </div>
                        <p x-show="selectedVehicleId && !selectedVehicleHasSopir" class="mt-2 text-xs text-amber-600">
                            <i class="fas fa-info-circle mr-1"></i>Kendaraan ini tersedia tanpa sopir.
                        </p>
                        @error('with_driver') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Duration (Rental) --}}
                    <div x-show="serviceType === 'rental'">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Durasi Sewa</label>
                        <select name="duration" @change="selectDuration($el)" x-bind:required="serviceType === 'rental'" x-bind:disabled="serviceType !== 'rental'" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                            <option value="">— Pilih durasi —</option>
                            <option value="0.5" @selected(old('duration') == '0.5')>12 Jam</option>
                            @for ($i = 1; $i <= 7; $i++)
                            <option value="{{ $i }}" @selected(old('duration') == $i)>{{ $i }} Hari</option>
                            @endfor
                        </select>
                        @error('duration') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Rental Total Price --}}
                    <div x-show="serviceType === 'rental' && rentalTotal > 0" class="md:col-span-2">
                        <div class="rounded-xl border-2 border-dashed border-[#E8A33D] bg-amber-50 p-4 text-center">
                            <p class="text-sm text-amber-700">Total Harga Sewa</p>
                            <p class="font-[IBM_Plex_Mono] text-2xl font-bold text-blue-900" x-text="rentalTotalFormatted"></p>
                        </div>
                    </div>
                </div>

                @if ($vehicles->isEmpty())
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm text-amber-700">Belum ada kendaraan tersedia. Silakan cek kembali nanti.</p>
                </div>
                @endif

                <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6">
                    <button type="submit" class="rounded-xl bg-blue-900 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">Simpan Booking</button>
                    <a href="{{ route('bookings.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
     {{-- Vehicle Picker Modal --}}
     <x-modal name="vehicle-picker" maxWidth="3xl">
        <div class="p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Pilih Kendaraan</h2>
                <button x-on:click="$dispatch('close')" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            @if ($vehicles->isNotEmpty())
            <div class="grid max-h-[60vh] gap-4 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vehicles as $v)
                @php
                    $vehiclePickerData = [
                        'id' => $v->id,
                        'nama' => $v->nama,
                        'plat_nomor' => $v->plat_nomor,
                        'foto' => $v->foto_url,
                        'jenis' => $v->jenis,
                        'harga' => $v->harga_sewa_dengan_sopir_per_hari,
                        'harga_tanpa_sopir' => $v->harga_sewa_tanpa_sopir_per_hari,
                        'harga_dengan_sopir' => $v->harga_sewa_dengan_sopir_per_hari,
                        'has_sopir' => (bool) $v->sopir,
                    ];
                @endphp
                <div @click="$dispatch('pick-vehicle', @js($vehiclePickerData))"
                    x-show="serviceType !== 'rental' || isRentalVehicleAvailable({{ $v->id }})"
                    class="cursor-pointer rounded-xl border-2 border-slate-200 bg-white p-4 transition hover:border-blue-400 hover:shadow-md"
                    :class="window.__bookingData.selectedVehicleId === {{ $v->id }} ? 'border-blue-900 bg-blue-50' : ''">
                    <div class="mb-3 overflow-hidden rounded-lg bg-slate-100">
                        @if ($v->foto)
                            <img src="{{ $v->foto_url }}" alt="{{ $v->nama }}" class="h-40 w-full object-cover">
                        @else
                            <div class="flex h-40 w-full items-center justify-center">
                                <i class="fas fa-car text-4xl text-slate-300"></i>
                            </div>
                        @endif
                    </div>
                    <p class="font-semibold text-slate-800">{{ $v->nama }}</p>
                    <p class="font-[IBM_Plex_Mono] text-sm text-slate-600">{{ $v->plat_nomor }}</p>
                    <p class="text-xs capitalize text-slate-500">{{ $v->jenis }}</p>
                    @if ($v->sopir)
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                            <i class="fas fa-user-tie"></i>Dengan Sopir
                        </span>
                        <p class="mt-1 text-xs text-slate-500"><i class="fas fa-user mr-1"></i>{{ $v->sopir->name }}</p>
                    @else
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">
                            <i class="fas fa-user-slash"></i>Tanpa Sopir
                        </span>
                    @endif
                    <p class="mt-2 font-[IBM_Plex_Mono] text-base font-bold text-blue-900">
                        Rp {{ number_format($v->harga_sewa_tanpa_sopir_per_hari, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">tanpa / hari</span>
                        <span class="mx-1 text-slate-300" aria-hidden="true">/</span>
                        Rp {{ number_format($v->harga_sewa_dengan_sopir_per_hari, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">dengan / hari</span>
                    </p>
                </div>
                @endforeach
            </div>
            @else
            <p class="py-8 text-center text-slate-500">Belum ada kendaraan tersedia.</p>
            @endif

            <div class="mt-5 flex justify-end">
                <button x-on:click="$dispatch('close')"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                    Batal
                </button>
            </div>
         </div>
     </x-modal>
     </div>
@endsection
