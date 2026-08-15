<x-guest-layout title="{{ __('Login') }}" subtitle="{{ __('Masuk untuk mengakses dashboard Anda') }}" footer="{{ __('Belum punya akun?') }}" footerLink="{{ route('register') }}" footerText="{{ __('Daftar Sekarang') }}">
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope text-blue-900 mr-2"></i>{{ __('Email') }}
            </label>
            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="{{ __('nama@email.com') }}">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock text-blue-900 mr-2"></i>{{ __('Password') }}
            </label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent transition"
                placeholder="••••••••">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                    class="w-4 h-4 text-blue-900 border-gray-300 rounded focus:ring-blue-900 focus:ring-2">
                <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                    {{ __('Remember me') }}
                </label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-blue-900 hover:text-blue-800 font-medium transition">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-800 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition transform hover:scale-[1.02]">
            <i class="fas fa-sign-in-alt mr-2"></i>{{ __('Log in') }}
        </button>
    </form>

    <!-- Demo Credentials Info -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <p class="text-sm font-medium text-blue-900 mb-2"><i class="fas fa-info-circle mr-1"></i> {{ __('Akun Demo') }}:</p>
        <div class="text-xs text-blue-800 space-y-1">
            <p><strong>Admin:</strong> admin@asrgo.test / password</p>
            <p><strong>Customer:</strong> customer@asrgo.test / password</p>
            <p><strong>Mitra:</strong> mitra@asrgo.test / password</p>
            <p><strong>Driver:</strong> driver@asrgo.test / password</p>
        </div>
    </div>
</x-guest-layout>
