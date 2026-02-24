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
                {{-- Payment Error Flash --}}
                @if (session('payment_error'))
                    <div class="bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ {{ session('payment_error') }}
                    </div>
                @endif
                <!-- Order Details -->
                <div>
                    <p class="text-text-secondary text-sm">Kode Pesanan</p>
                    <p class="font-bold text-text-primary text-lg">{{ $order->code }}</p>
                </div>

                <div>
                    <p class="text-text-secondary text-sm">Total Pembayaran</p>
                    <p class="font-bold text-primary text-2xl">{{ $order->formatted_total }}</p>
                </div>

                <!-- Payment Method: Duitku (Redirect) or QRIS (Mock) -->
                <div class="flex justify-center flex-col items-center space-y-4">
                    @if ($order->payment_url)
                        @if (str_contains($order->payment_url, 'duitku.com'))
                            <!-- Duitku Redirect Button -->
                            <div class="text-center space-y-2">
                                <p class="text-sm text-text-secondary">Silakan selesaikan pembayaran di halaman Duitku</p>
                                <a href="{{ $order->payment_url }}" target="_blank"
                                    class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                                    Lanjut ke Pembayaran
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                                <p class="text-xs text-text-secondary mt-2">(Klik tombol di atas untuk membuka halaman
                                    pembayaran)</p>
                            </div>
                        @else
                            <!-- Mock QR Code (Legacy/Testing) -->
                            <div class="p-4 border-2 border-dashed border-primary/30 rounded-lg bg-surface relative">
                                <img src="{{ $order->payment_url }}" alt="QRIS Code" class="w-48 h-48 object-contain">
                            </div>
                        @endif
                    @else
                        <div
                            class="w-48 h-auto flex flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-6 text-center space-y-3">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <p class="text-sm text-gray-500">Pembayaran belum siap</p>
                            <a href="{{ route('payment.show', $order->code) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Coba Lagi
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Timer and Logic Container -->
                <div x-data="{
                    expiry: new Date('{{ $order->payment_expires_at ? $order->payment_expires_at->toIso8601String() : now()->toIso8601String() }}').getTime(),
                    timeLeft: '00:00',
                    expired: false,
                    checking: false,
                    redirecting: false,
                    interval: null,
                    pollingInterval: null,
                
                    init() {
                        this.updateTimer();
                        this.interval = setInterval(() => {
                            this.updateTimer();
                        }, 1000);
                        // Polling fallback every 5 seconds
                        this.pollingInterval = setInterval(() => {
                            if (!this.redirecting) {
                                this.checkStatus(true);
                            }
                        }, 5000);
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
                
                    async checkStatus(silent = false) {
                        if (this.redirecting) return;
                        if (!silent) this.checking = true;
                        try {
                            const response = await fetch('{{ route('payment.check', $order->code) }}');
                            const data = await response.json();
                
                            if (data.status === 'paid') {
                                this.redirecting = true;
                                clearInterval(this.pollingInterval); // Stop polling
                                window.location.href = '{{ route('tracking.show', $order->code) }}';
                            } else if (data.status === 'failed') {
                                window.location.reload();
                            } else {
                                if (!silent) alert('Pembayaran belum terdeteksi. Silakan tunggu sebentar.');
                            }
                        } catch (e) {
                            console.error(e);
                        } finally {
                            if (!silent) this.checking = false;
                        }
                    }
                }" class="text-center">
                    <p class="text-sm text-text-secondary mb-1">Sisa Waktu Pembayaran</p>
                    <p class="text-2xl font-mono font-bold text-warning" x-text="timeLeft"></p>
                    <p x-show="expired" style="display: none;" class="text-error text-sm font-bold mt-2">Waktu Habis!</p>

                    <!-- Instructions -->
                    <div class="text-sm text-text-secondary bg-surface p-4 rounded-lg text-left space-y-2 mt-6 mb-6">
                        <p class="font-bold text-text-primary">Cara Pembayaran:</p>
                        @if (isset($order->payment_url) && str_contains($order->payment_url, 'duitku.com'))
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Klik tombol <strong>Lanjut ke Pembayaran</strong> di atas.</li>
                                <li>Anda akan diarahkan ke halaman Duitku.</li>
                                <li>Pilih metode pembayaran (QRIS, E-Wallet, Transfer Bank, dll).</li>
                                <li>Selesaikan pembayaran sesuai instruksi di layar.</li>
                                <li>Setelah sukses, Anda akan diarahkan kembali ke halaman ini otomatis.</li>
                            </ol>
                        @else
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Buka aplikasi e-wallet (GoPay, OVO, Dana, LinkAja, dll) atau Mobile Banking.</li>
                                <li>Pilih menu <strong>Scan QR</strong> atau <strong>Bayar</strong>.</li>
                                <li>Arahkan kamera ke kode QR di atas.</li>
                                <li>Periksa detail pembayaran dan konfirmasi.</li>
                                <li>Pembayaran akan terverifikasi otomatis.</li>
                            </ol>
                        @endif
                    </div>

                    <!-- Manual Check Button -->
                    <button @click="checkStatus()"
                        class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                        :disabled="checking || redirecting">
                        <template x-if="checking || redirecting">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="redirecting ? 'Mengalihkan...' : 'Mengecek...'"></span>
                            </div>
                        </template>
                        <template x-if="!checking && !redirecting">
                            <span>Cek Status Pembayaran</span>
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
