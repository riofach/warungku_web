@extends('layouts.app')

@section('title', 'Status Pesanan - ' . $order->code)

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">

        {{-- Success flash message --}}
        @if (session('success'))
            <div class="bg-success/10 border border-success text-success px-4 py-3 rounded-lg flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="card p-5 mb-4">
            {{-- Kode Pesanan & Waktu --}}
            <div class="text-center mb-4">
                <p class="text-xs text-text-tertiary font-medium uppercase tracking-wide mb-1">Kode Pesanan</p>
                <div class="flex items-center justify-center gap-2">
                    <span class="text-xl font-mono font-bold text-primary tracking-wider">{{ $order->code }}</span>
                    <button
                        @click="navigator.clipboard.writeText('{{ $order->code }}'); $dispatch('toast', { message: 'Kode berhasil disalin!', type: 'success' })"
                        class="p-1.5 hover:bg-surface rounded-full transition-colors text-text-secondary hover:text-primary"
                        title="Salin Kode">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-text-secondary mt-1">
                    {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>

            {{-- Status Badge — center --}}
            @php
                $badgeClass = match ($order->status) {
                    'pending' => 'bg-warning/15 text-warning border border-warning/40',
                    'paid' => 'bg-blue-100 text-blue-700 border border-blue-300',
                    'processing' => 'bg-indigo-100 text-indigo-700 border border-indigo-300',
                    'ready' => 'bg-success/15 text-success border border-success/40',
                    'delivered' => 'bg-teal-100 text-teal-700 border border-teal-300',
                    'completed' => 'bg-success/15 text-success border border-success/40',
                    'cancelled', 'failed' => 'bg-error/15 text-error border border-error/40',
                    default => 'bg-gray-100 text-gray-700 border border-gray-300',
                };
            @endphp
            <div class="w-full flex justify-center">
                <span
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-semibold {{ $badgeClass }}">
                    @if (in_array($order->status, ['cancelled', 'failed']))
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @elseif ($order->status === 'completed')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse shrink-0"></span>
                    @endif
                    <span class="leading-none">{{ $order->status_label }}</span>
                </span>
            </div>
        </div>

        {{-- Order Timeline --}}
        @php
            $steps = [
                [
                    'key' => 'pending',
                    'label' => 'Pesanan Dibuat',
                    'icon' =>
                        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                ],
                [
                    'key' => 'paid',
                    'label' => 'Dibayar',
                    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                ],
                [
                    'key' => 'processing',
                    'label' => 'Dikemas',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                ],
                [
                    'key' => 'ready',
                    'label' => 'Siap Diambil/Antar',
                    'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                ],
                [
                    'key' => 'completed',
                    'label' => 'Selesai',
                    'icon' =>
                        'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                ],
            ];

            $statusOrder = ['pending', 'paid', 'processing', 'ready', 'delivered', 'completed'];
            $isCancelled = in_array($order->status, ['cancelled', 'failed']);
            $currentIndex = array_search($order->status === 'delivered' ? 'ready' : $order->status, $statusOrder);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        @endphp

        <div class="card p-5 mb-4">
            <h2 class="font-semibold text-sm uppercase tracking-wide text-text-secondary mb-6">
                Status Perjalanan Pesanan
            </h2>

            @if ($isCancelled)
                {{-- Cancelled/Failed State --}}
                <div class="flex items-center gap-3 p-4 bg-error/10 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-error flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-error">{{ $order->status_label }}</p>
                        <p class="text-sm text-text-secondary">Pesanan ini telah dibatalkan atau gagal diproses.</p>
                    </div>
                </div>
            @else
                {{-- Normal Timeline --}}
                <div class="space-y-0">
                    @foreach ($steps as $index => $step)
                        @php
                            $stepIndex = array_search($step['key'], $statusOrder);
                            $isDone = $stepIndex <= $currentIndex;
                            $isActive = $stepIndex === $currentIndex;
                            $isCompleted = $isDone && !$isActive; // truly past, not current
                            $isLast = $index === count($steps) - 1;
                            $nextIsActive = !$isLast && $stepIndex + 1 === $currentIndex;
                        @endphp

                        {{-- Step row: icon + label side by side, vertically centered --}}
                        <div class="flex items-center gap-3">

                            {{-- Icon column: fixed w-9 so connector below can line up --}}
                            <div class="w-9 shrink-0 flex items-center justify-center">
                                @if ($isActive)
                                    {{-- Active: filled circle + outer ping ring --}}
                                    <div class="relative flex items-center justify-center">
                                        <span class="absolute w-12 h-12 rounded-full bg-primary animate-ping"
                                            style="opacity:0.18;"></span>
                                        <div
                                            class="relative z-10 w-9 h-9 rounded-full flex items-center justify-center bg-primary border-2 border-primary shadow">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ $step['icon'] }}" />
                                            </svg>
                                        </div>
                                    </div>
                                @elseif ($isCompleted)
                                    {{-- Completed: solid blue circle --}}
                                    <div
                                        class="w-9 h-9 rounded-full flex items-center justify-center bg-primary border-2 border-primary">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $step['icon'] }}" />
                                        </svg>
                                    </div>
                                @else
                                    {{-- Upcoming: grey circle --}}
                                    <div
                                        class="w-9 h-9 rounded-full flex items-center justify-center bg-background border-2 border-border">
                                        <svg class="w-4 h-4 text-text-tertiary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $step['icon'] }}" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Label  --}}
                            <div class="flex-1 min-w-0 py-3">
                                <p
                                    class="font-semibold text-sm leading-tight
                                    {{ $isDone ? 'text-text-primary' : 'text-text-tertiary' }}
                                    {{ $isActive ? 'text-primary' : '' }}">
                                    {{ $step['label'] }}
                                </p>
                                @if ($isActive)
                                    <p class="text-xs text-primary font-medium mt-1 flex items-center gap-1">
                                        <span class="animate-pulse">● Sedang diproses...</span>
                                    </p>
                                @elseif ($isCompleted && $step['key'] === 'pending')
                                    <p class="text-xs text-text-secondary mt-0.5">
                                        {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Connector between steps --}}
                        @if (!$isLast)
                            <div class="w-9 shrink-0 flex justify-center">
                                @if ($nextIsActive)
                                    {{-- 3 breathing dots leading to the active step --}}
                                    <div class="flex flex-col items-center gap-[5px] py-1">
                                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"
                                            style="animation-duration:1.2s; animation-delay:0s"></span>
                                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"
                                            style="animation-duration:1.2s; animation-delay:0.4s"></span>
                                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"
                                            style="animation-duration:1.2s; animation-delay:0.8s"></span>
                                    </div>
                                @else
                                    <div
                                        class="w-0.5 min-h-[28px] rounded-full {{ $isCompleted ? 'bg-primary' : 'bg-border' }}">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Order Details --}}
        <div class="card p-5 mb-4">
            <h2 class="font-semibold text-sm uppercase tracking-wide text-text-secondary mb-4">
                Detail Pesanan
            </h2>

            {{-- Customer Info --}}
            <div class="grid grid-cols-2 gap-x-4 gap-y-3 mb-4 pb-4 border-b border-border text-sm">
                <div>
                    <p class="text-text-tertiary text-xs mb-0.5">Nama Pemesan</p>
                    <p class="font-medium">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <p class="text-text-tertiary text-xs mb-0.5">Jenis Pengiriman</p>
                    <p class="font-medium">
                        @if ($order->delivery_type === 'delivery')
                            🚚 Antar
                            @if ($order->housingBlock)
                                <span class="text-text-secondary">({{ $order->housingBlock->name }})</span>
                            @endif
                        @else
                            🏪 Ambil Sendiri
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-text-tertiary text-xs mb-0.5">Metode Bayar</p>
                    <p class="font-medium">{{ strtoupper($order->payment_method) }}</p>
                </div>
                <div>
                    <p class="text-text-tertiary text-xs mb-0.5">Waktu Pesan</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            {{-- Items List --}}
            <h3 class="text-sm font-medium text-text-secondary mb-3">Item Pesanan</h3>
            <div class="space-y-2 mb-4">
                @foreach ($order->orderItems as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <span
                                class="w-6 h-6 rounded bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">
                                {{ $item->quantity }}
                            </span>
                            <span class="text-sm truncate">{{ $item->item?->name ?? 'Item dihapus' }}</span>
                        </div>
                        <span class="text-sm font-medium shrink-0 ml-2">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-between pt-3 border-t border-border">
                <span class="font-semibold">Total Pembayaran</span>
                <span class="font-bold text-lg text-primary">
                    Rp {{ number_format($order->total, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Download Invoice --}}
            @php
                $canDownload = in_array($order->status, ['paid', 'processing', 'ready', 'delivered', 'completed']);
            @endphp
            <button
                @if ($canDownload) @click="$dispatch('toast', { message: 'Fitur unduh invoice akan segera hadir!', type: 'warning' })"
            @else
                disabled @endif
                title="{{ $canDownload ? 'Unduh Invoice' : 'Invoice tersedia setelah pembayaran dikonfirmasi' }}"
                class="flex items-center justify-center gap-2 flex-1 px-4 py-2.5 rounded-lg font-medium text-sm border transition-colors
                {{ $canDownload
                    ? 'border-primary text-primary hover:bg-primary/10 cursor-pointer'
                    : 'border-border text-text-tertiary cursor-not-allowed bg-background/50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Invoice
            </button>

            {{-- Chat Admin --}}
            @php
                $waNumber = \App\Models\Setting::getValue('whatsapp_number', '');
                $waText = urlencode("Halo Admin, saya ingin menanyakan pesanan dengan kode: {$order->code}");
                $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text={$waText}" : '#';
            @endphp
            <a href="{{ $waUrl }}"
                @if (!$waNumber) @click.prevent="$dispatch('toast', { message: 'Nomor WhatsApp belum dikonfigurasi', type: 'error' })" @endif
                target="_blank"
                class="flex items-center justify-center gap-2 flex-1 px-4 py-2.5 rounded-lg font-medium text-sm bg-[#25D366] text-white hover:bg-[#1ebe5a] transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12c0 2.127.562 4.12 1.536 5.854L.057 23.882a.5.5 0 00.611.611l6.028-1.478A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.94 9.94 0 01-5.13-1.422l-.37-.22-3.528.865.882-3.53-.243-.385A10 10 0 1112 22z" />
                </svg>
                Chat Admin
            </a>
        </div>

        {{-- Navigation Links --}}
        <div class="flex flex-col sm:flex-row gap-3 mt-4">
            <a href="{{ route('tracking.index') }}" class="btn-outline w-full text-center text-sm">
                ← Lacak Pesanan Lain
            </a>
            <a href="{{ route('home') }}" class="btn-primary w-full text-center text-sm">
                Kembali Belanja
            </a>
        </div>
    </div>
@endsection
