<x-guest-layout title="{{ __('Daftar Akun') }}" subtitle="{{ __('Buat akun baru untuk mulai menggunakan layanan ASR GO') }}" footer="{{ __('Sudah punya akun?') }}" footerLink="{{ route('login') }}" footerText="{{ __('Login Sekarang') }}">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user text-blue-900 mr-2"></i>{{ __('Name') }}
            </label>
            <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="{{ __('Masukkan nama lengkap') }}">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope text-blue-900 mr-2"></i>{{ __('Email') }}
            </label>
            <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="{{ __('nama@email.com') }}">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role Selection -->
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user-tag text-blue-900 mr-2"></i>{{ __('Daftar sebagai') }}
            </label>
            <select id="role" name="role"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition bg-white">
                <option value="customer">{{ __('Customer - Pelanggan') }}</option>
                <option value="mitra">{{ __('Mitra - Partner') }}</option>
            </select>
            @error('role')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock text-blue-900 mr-2"></i>{{ __('Password') }}
            </label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="{{ __('Minimal 8 karakter') }}">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock text-blue-900 mr-2"></i>{{ __('Confirm Password') }}
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="{{ __('Ulangi password') }}">
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Terms Checkbox -->
        <div class="flex items-start">
            <input id="terms" type="checkbox" name="terms" required
                class="w-4 h-4 text-blue-900 border-gray-300 rounded focus:ring-blue-900 focus:ring-2 mt-1">
            <label for="terms" class="ml-2 block text-sm text-gray-700">
                @lang('Saya setuju dengan :terms dan :privacy', ['terms' => __('Syarat & Ketentuan'), 'privacy' => __('Kebijakan Privasi')])
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-800 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition transform hover:scale-[1.02]">
            <i class="fas fa-user-plus mr-2"></i>{{ __('Register') }}
        </button>
    </form>

    <!-- Role Info -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <p class="text-sm font-medium text-gray-900 mb-2"><i class="fas fa-info-circle mr-1 text-blue-900"></i> {{ __('Informasi Role') }}:</p>
        <div class="text-xs text-gray-700 space-y-1">
            <p><strong>Customer:</strong> {{ __('Customer: Pesan layanan travel, rental mobil, dan airport transfer') }}</p>
            <p><strong>Mitra:</strong> {{ __('Mitra: Kelola armada kendaraan dan terima pembagian pendapatan') }}</p>
        </div>
    </div>
</x-guest-layout>