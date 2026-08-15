<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            .font-display {
                font-family: 'Barlow Condensed', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Left Side - Branding -->
            <div class="lg:w-1/2 bg-gradient-to-br from-blue-900 to-blue-950 p-8 lg:p-12 flex flex-col justify-center">
                <div class="mb-8">
                    <a href="/" class="text-white font-display text-3xl font-bold tracking-wide">
                        {{ __('ASR GO') }}
                    </a>
                    <p class="text-blue-200 mt-2 text-lg">{{ __('CV. IzalhadjiTravel') }}</p>
                    <p class="text-blue-300 mt-1 text-sm">{{ __('Travel & Rental Mobil Flores') }}</p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-white/10 p-3 rounded-lg">
                            <i class="fas fa-users text-blue-300 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">{{ __('Multi-Role Platform') }}</h3>
                            <p class="text-blue-200 text-sm">{{ __('Admin, Mitra, Driver, dan Customer dalam satu sistem terintegrasi') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-white/10 p-3 rounded-lg">
                            <i class="fas fa-car text-blue-300 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">{{ __('Layanan Lengkap') }}</h3>
                            <p class="text-blue-200 text-sm">{{ __('Travel, Rental Mobil, dan Airport Transfer') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-white/10 p-3 rounded-lg">
                            <i class="fas fa-shield-alt text-blue-300 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">{{ __('Sistem Pembayaran Aman') }}</h3>
                            <p class="text-blue-200 text-sm">{{ __('Integrasi Midtrans dengan revenue sharing otomatis') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 hidden lg:block">
                    <p class="text-blue-300 text-sm">© 2026 {{ __('CV. IzalhadjiTravel') }}. {{ __('Smart Intercity Travel & Rental System') }}</p>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="lg:w-1/2 bg-gray-50 p-8 lg:p-12 flex flex-col justify-center">
                <div class="max-w-md mx-auto w-full">
                    <div class="mb-8 text-center lg:text-left">
                        <h1 class="font-display text-3xl font-bold text-gray-900 mb-2">
                            {{ $title ?? __('Login') }}
                        </h1>
                        <p class="text-gray-600">{{ $subtitle ?? __('Masuk untuk mengakses dashboard Anda') }}</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        {{ $slot }}
                    </div>

                    <div class="mt-6 text-center">
                        <p class="text-gray-600 text-sm">
                            {{ $footer ?? __('Belum punya akun?') }}
                            <a href="{{ $footerLink ?? route('register') }}" class="text-blue-900 font-semibold hover:text-blue-800 transition">
                                {{ $footerText ?? __('Daftar Sekarang') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
