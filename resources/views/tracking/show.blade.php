@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="card p-6 border-l-4 border-l-primary">
        <div class="text-center mb-6">
            <div class="text-5xl mb-4 animate-bounce">🎉</div>
            <h1 class="text-2xl font-bold mb-2">Pesanan Berhasil Dibuat!</h1>
            <p class="text-text-secondary">Terima kasih telah berbelanja di WarungLuthfan</p>
        </div>

        <div class="bg-background rounded-lg p-4 mb-6 text-center">
            <p class="text-sm text-text-secondary mb-1">Kode Pesanan Anda</p>
            <div class="flex items-center justify-center gap-2">
                <span class="text-2xl font-mono font-bold text-primary tracking-wider" id="order-code">{{ $order->code }}</span>
                <button 
                    @click="navigator.clipboard.writeText('{{ $order->code }}'); $dispatch('toast', { message: 'Kode berhasil disalin!', type: 'success' })"
                    class="p-2 hover:bg-surface rounded-full transition-colors text-text-secondary hover:text-primary"
                    title="Salin Kode"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-text-tertiary mt-2">Simpan kode ini untuk melacak status pesanan</p>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between py-2 border-b border-border">
                <span class="text-text-secondary">Status</span>
                <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-border">
                <span class="text-text-secondary">Total Pembayaran</span>
                <span class="font-bold text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-border">
                <span class="text-text-secondary">Metode Pembayaran</span>
                <span class="font-medium">{{ ucfirst($order->payment_method) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-border">
                <span class="text-text-secondary">Pengambilan</span>
                <span class="font-medium">
                    {{ ucfirst($order->delivery_type) }}
                    @if($order->housing_block)
                        ({{ $order->housing_block->name }})
                    @endif
                </span>
            </div>
        </div>

        <div class="mt-8 space-y-3">
            <a href="{{ route('home') }}" class="btn-primary w-full block text-center">
                Kembali Belanja
            </a>
            <a href="{{ route('tracking.index') }}" class="btn-outline w-full block text-center">
                Lacak Pesanan Lain
            </a>
        </div>
    </div>
</div>
@endsection
