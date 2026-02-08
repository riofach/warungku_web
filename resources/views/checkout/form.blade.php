@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-6 pb-24 md:pb-6" x-data="checkoutForm()">
    <h1 class="text-2xl font-bold mb-6">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" @submit="handleSubmit">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column: Form -->
            <div class="space-y-6">
                
                <!-- Customer Info -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Informasi Pembeli</h2>
                    
                    <!-- Name -->
                    <div class="mb-4">
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="customer_name" id="customer_name" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('customer_name') }}"
                            placeholder="Masukkan nama lengkap">
                        @error('customer_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pengambilan</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex items-center justify-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 focus-within:ring-2 focus-within:ring-blue-500 transition-colors"
                                :class="{'border-blue-500 bg-blue-50': deliveryType === 'delivery', 'border-gray-200': deliveryType !== 'delivery'}">
                                <input type="radio" name="delivery_type" value="delivery" class="sr-only" x-model="deliveryType">
                                <span class="font-medium" :class="{'text-blue-700': deliveryType === 'delivery'}">Diantar</span>
                            </label>
                            
                            <label class="relative flex items-center justify-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 focus-within:ring-2 focus-within:ring-blue-500 transition-colors"
                                :class="{'border-blue-500 bg-blue-50': deliveryType === 'pickup', 'border-gray-200': deliveryType !== 'pickup'}">
                                <input type="radio" name="delivery_type" value="pickup" class="sr-only" x-model="deliveryType">
                                <span class="font-medium" :class="{'text-blue-700': deliveryType === 'pickup'}">Ambil Sendiri</span>
                            </label>
                        </div>
                        @error('delivery_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location (Block) - Only if delivery -->
                    <div class="mb-4" x-show="deliveryType === 'delivery'" x-transition>
                        <label for="housing_block_id" class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Blok)</label>
                        <select name="housing_block_id" id="housing_block_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :required="deliveryType === 'delivery'">
                            <option value="">Pilih Blok Rumah</option>
                            @foreach($housingBlocks as $block)
                                <option value="{{ $block->id }}" {{ old('housing_block_id') == $block->id ? 'selected' : '' }}>
                                    {{ $block->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('housing_block_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Metode Pembayaran</h2>
                    
                    <div class="space-y-3">
                        <!-- QRIS -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="{'border-blue-500 bg-blue-50': paymentMethod === 'qris', 'border-gray-200': paymentMethod !== 'qris', 'opacity-50 cursor-not-allowed': deliveryType === 'pickup'}">
                            <input type="radio" name="payment_method" value="qris" class="sr-only" x-model="paymentMethod" 
                                :disabled="deliveryType === 'pickup'">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">QRIS (Scan Barcode)</span>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Instan</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Pembayaran non-tunai via GoPay, OVO, Dana, dll.</p>
                            </div>
                        </label>
                        
                        <!-- Cash -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="{'border-blue-500 bg-blue-50': paymentMethod === 'tunai', 'border-gray-200': paymentMethod !== 'tunai', 'opacity-50 cursor-not-allowed': deliveryType === 'delivery'}">
                            <input type="radio" name="payment_method" value="tunai" class="sr-only" x-model="paymentMethod"
                                :disabled="deliveryType === 'delivery'">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">Tunai (Cash)</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1" x-text="deliveryType === 'pickup' ? 'Bayar saat ambil barang' : 'Bayar ditempat (COD) tidak tersedia'">Bayar Tunai</p>
                            </div>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    
                    <!-- Logic Helper Text -->
                    <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 text-sm rounded-md" x-show="deliveryType === 'delivery'">
                        <span class="font-semibold">Catatan:</span> Pesan antar hanya menerima pembayaran QRIS.
                    </div>
                    <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 text-sm rounded-md" x-show="deliveryType === 'pickup'">
                         <span class="font-semibold">Catatan:</span> Ambil sendiri hanya menerima pembayaran Tunai.
                    </div>
                </div>

            </div>

            <!-- Right Column: Order Summary -->
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="text-lg font-semibold mb-4">Ringkasan Pesanan</h2>
                    
                    <div class="flow-root">
                        <ul role="list" class="-my-4 divide-y divide-gray-200">
                            @foreach($cartItems as $item)
                            <li class="flex py-4">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                    @if(isset($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover object-center">
                                    @else
                                        <div class="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-4 flex flex-1 flex-col">
                                    <div>
                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                            <h3>{{ $item['name'] }}</h3>
                                            <p class="ml-4">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-1 items-end justify-between text-sm">
                                        <p class="text-gray-500">Qty {{ $item['quantity'] }}</p>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-gray-200 mt-6 pt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Total Belanja</p>
                            <p class="text-sm font-medium text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Ongkos Kirim</p>
                            <p class="text-sm font-medium text-green-600">Gratis</p>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <p class="text-base font-bold text-gray-900">Total Bayar</p>
                            <p class="text-base font-bold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <!-- Desktop Button -->
                    <div class="hidden md:block mt-6">
                         <button type="submit" form="checkout-form"
                            class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            :disabled="isSubmitting || !isValid()"
                            :class="{'opacity-75 cursor-wait': isSubmitting}">
                            
                            <!-- Loading Spinner -->
                            <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            
                            <span x-text="isSubmitting ? 'Memproses...' : 'Buat Pesanan'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Mobile Sticky Bottom Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 md:hidden z-40 safe-area-inset-bottom">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs text-gray-500">Total Bayar</p>
            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>
        <button type="submit" form="checkout-form"
            class="flex-1 flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            :disabled="isSubmitting || !isValid()"
            :class="{'opacity-75 cursor-wait': isSubmitting}">
            
            <!-- Loading Spinner -->
            <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            
            <span x-text="isSubmitting ? 'Memproses...' : 'Buat Pesanan'"></span>
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutForm', () => ({
            deliveryType: @json(old('delivery_type', 'delivery')),
            paymentMethod: @json(old('payment_method', '')),
            isSubmitting: false,
            
            init() {
                // Auto-select payment method based on initial delivery type if needed
                this.$watch('deliveryType', (value) => {
                    if (value === 'delivery') {
                        this.paymentMethod = 'qris';
                    } else if (value === 'pickup') {
                        this.paymentMethod = 'tunai';
                    }
                });
                
                // Initial check
                if (this.deliveryType === 'delivery' && this.paymentMethod !== 'qris') {
                     this.paymentMethod = 'qris';
                } else if (this.deliveryType === 'pickup' && this.paymentMethod !== 'tunai') {
                     this.paymentMethod = 'tunai';
                }
            },
            
            isValid() {
                // Basic client side check
                if (this.deliveryType === 'delivery') {
                    return this.paymentMethod === 'qris';
                }
                if (this.deliveryType === 'pickup') {
                    return this.paymentMethod === 'tunai';
                }
                return false;
            },

            handleSubmit(e) {
                if (this.isSubmitting) {
                    e.preventDefault();
                    return;
                }
                this.isSubmitting = true;
            }
        }));
    });
</script>
@endpush
