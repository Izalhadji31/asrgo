@extends($layout)

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Notifikasi</h1>
                <p class="mt-1 text-sm text-slate-500">Informasi terbaru tentang booking dan pembayaran Anda.</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Tandai semua dibaca</button>
            </form>
        </div>

        @if (session('success'))
            <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($notifications as $notification)
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5 {{ $notification->read_at ? '' : 'bg-blue-50/50' }}">
                    <div>
                        <p class="font-medium text-slate-800">{{ $notification->message }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->format('d M Y H:i') }}</p>
                    </div>
                    @if (!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button class="whitespace-nowrap text-xs font-medium text-blue-900 hover:underline">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
@endsection
