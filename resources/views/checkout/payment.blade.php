@extends('layouts.app')

@section('title', 'Pembayaran QRIS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-primary px-6 py-4 text-center">
            <h1 class="text-white font-bold text-lg">Pembayaran QRIS</h1>
            <p class="text-white/80 text-sm mt-1">Scan QR Code di bawah untuk membayar</p>
        </div>

        <div class="p-6 text-center space-y-6">
            <!-- Order Details -->
            <div>
                <p class="text-text-secondary text-sm">Kode Pesanan</p>
                <p class="font-bold text-text-primary text-lg">{{ $order->code }}</p>
            </div>
            
            <div>
                <p class="text-text-secondary text-sm">Total Pembayaran</p>
                <p class="font-bold text-primary text-2xl">{{ $order->formatted_total }}</p>
            </div>

            <!-- QR Code -->
            <div class="flex justify-center">
                <div class="p-4 border-2 border-dashed border-primary/30 rounded-lg bg-surface relative">
                     @if($order->payment_url)
                        <img src="{{ $order->payment_url }}" alt="QRIS Code" class="w-48 h-48 object-contain">
                     @else
                        <div class="w-48 h-48 flex items-center justify-center bg-gray-100 text-gray-400">
                            QR Not Available
                        </div>
                     @endif
                </div>
            </div>

            <!-- Timer and Logic Container -->
            <div x-data="{
                expiry: new Date('{{ $order->payment_expires_at->toIso8601String() }}').getTime(),
                timeLeft: '00:00',
                expired: false,
                checking: false,
                interval: null,

                init() {
                    this.updateTimer();
                    this.interval = setInterval(() => {
                        this.updateTimer();
                    }, 1000);
                },

                updateTimer() {
                    const now = new Date().getTime();
                    const distance = this.expiry - now;

                    if (distance < 0) {
                        clearInterval(this.interval);
                        this.timeLeft = '00:00';
                        this.expired = true;
                    } else {
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        this.timeLeft = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }
                },
                
                async checkStatus() {
                    this.checking = true;
                    try {
                        const response = await fetch('{{ route('payment.check', $order->code) }}');
                        const data = await response.json();
                        
                        if (data.status === 'paid') {
                            window.location.href = '{{ route('tracking.show', $order->code) }}';
                        } else if (data.status === 'failed') {
                            window.location.reload();
                        } else {
                            alert('Pembayaran belum terdeteksi. Silakan tunggu sebentar.');
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.checking = false;
                    }
                }
            }" class="text-center">
                <p class="text-sm text-text-secondary mb-1">Sisa Waktu Pembayaran</p>
                <p class="text-2xl font-mono font-bold text-warning" x-text="timeLeft"></p>
                <p x-show="expired" style="display: none;" class="text-error text-sm font-bold mt-2">Waktu Habis!</p>
            
                <!-- Instructions -->
                <div class="text-sm text-text-secondary bg-surface p-4 rounded-lg text-left space-y-2 mt-6 mb-6">
                    <p class="font-bold text-text-primary">Cara Pembayaran:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Buka aplikasi e-wallet (GoPay, OVO, Dana, LinkAja, dll) atau Mobile Banking.</li>
                        <li>Pilih menu <strong>Scan QR</strong> atau <strong>Bayar</strong>.</li>
                        <li>Arahkan kamera ke kode QR di atas.</li>
                        <li>Periksa detail pembayaran dan konfirmasi.</li>
                        <li>Pembayaran akan terverifikasi otomatis.</li>
                    </ol>
                </div>

                <!-- Manual Check Button -->
                <button 
                    @click="checkStatus()" 
                    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                    :disabled="checking"
                >
                    <template x-if="checking">
                        <div class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mengecek...</span>
                        </div>
                    </template>
                    <template x-if="!checking">
                        <span>Saya Sudah Bayar</span>
                    </template>
                </button>
            </div>
            
            <a href="{{ route('home') }}" class="block text-primary text-sm hover:underline">Kembali ke Beranda</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Pass config to window for JS file
    window.supabaseConfig = {
        url: "{{ config('services.supabase.url') }}",
        key: "{{ config('services.supabase.anon_key') }}"
    };
</script>

<!-- Load Payment Realtime Logic -->
@vite(['resources/js/payment.js'])
<script>
    // Initialize subscription after script loads
    // Wait for window.subscribeToOrder to be available
    const checkPaymentLoaded = setInterval(() => {
        if (typeof window.subscribeToOrder === 'function') {
            window.subscribeToOrder("{{ $order->code }}");
            clearInterval(checkPaymentLoaded);
        }
    }, 100);
</script>
@endpush
@endsection
