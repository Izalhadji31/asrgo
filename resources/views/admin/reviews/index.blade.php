@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Ulasan Pelanggan</h1>
            <p class="text-sm text-slate-500">Rating dan komentar pelanggan terhadap layanan.</p>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Ulasan</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $totalReviews }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Rata-rata Rating</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">
                {{ $totalReviews > 0 ? $avgRating . ' / 5' : '-' }}
            </p>
        </article>
    </section>

    @if (session('success'))
        <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Booking</th>
                        <th class="px-5 py-3 font-medium">Layanan</th>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Rating</th>
                        <th class="px-5 py-3 font-medium">Komentar</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reviews as $review)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4 text-slate-600">{{ $review->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $review->customer?->name ?? '-' }}</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-600">#{{ $review->booking_id }}</td>
                            <td class="px-5 py-4">
                                @if ($review->booking?->service_type === 'travel')
                                    <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">Travel</span>
                                @else
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Rental</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $review->booking?->vehicle?->nama ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-[#E8A33D]">{{ str_repeat('★', $review->rating) }}</span><span class="text-slate-300">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </td>
                            <td class="max-w-xs px-5 py-4 text-slate-600">{{ $review->komentar ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada ulasan dari pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
