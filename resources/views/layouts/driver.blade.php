<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Sopir</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F5F4F0] text-slate-800 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="w-full bg-indigo-900 text-slate-100 lg:w-72">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-6">
                <div>
                    <p class="font-[Barlow_Condensed] text-2xl font-semibold tracking-wide">ASRGO</p>
                    <p class="text-sm text-indigo-300">Panel Sopir</p>
                </div>
                <div class="rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-indigo-900">Sopir</div>
            </div>

            <nav class="space-y-2 px-4 py-6" aria-label="Sidebar Navigation">
                @php
                $menu = [
                    ['label' => 'Dashboard', 'icon' => 'fa-solid fa-gauge-high', 'route' => 'driver.dashboard', 'active' => ['driver.dashboard']],
                    ['label' => 'Tugas Booking', 'icon' => 'fa-solid fa-clipboard-list', 'route' => 'driver.bookings.index', 'active' => ['driver.bookings.index']],
                    ['label' => 'Notifikasi', 'icon' => 'fa-solid fa-bell', 'route' => 'notifications.index', 'active' => ['notifications.*']],
                ];
                @endphp

                @foreach ($menu as $item)
                @php $isActive = !empty($item['active']) && request()->routeIs($item['active']); @endphp
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-indigo-900 {{ $isActive ? 'bg-white/10 text-white' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="text-base text-amber-400"><i class="{{ $item['icon'] }}"></i></span>
                    <span>{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex-1">
            <header class="relative border-b border-slate-200 bg-white/80 backdrop-blur">
                <div class="flex flex-col gap-3 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <p class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Panel Sopir</p>
                        <p class="text-sm text-slate-500">Lihat jadwal tugas dan selesaikan booking Anda.</p>
                    </div>
                    <div class="flex items-center gap-3" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full border border-slate-200 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-600 transition hover:border-amber-500 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <span>{{ auth()->user()?->name ?? 'Sopir' }}</span>
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-8 top-16 z-50 w-48 rounded-xl border border-slate-200 bg-white shadow-lg"
                             style="display: none;"
                             @click="open = false">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-medium text-slate-800">{{ auth()->user()?->name }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()?->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="p-1.5">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-red-600 transition hover:bg-red-50">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                                        <path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @hasSection('content')
                @yield('content')
                @else
                {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>
</body>
</html>
