@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

    @if(empty($cartItems))
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-xl font-semibold mb-2">Keranjang kosong</h2>
            <p class="text-text-secondary mb-4">Belum ada produk di keranjang Anda</p>
            <a href="{{ route('shop.index') }}" class="btn-primary inline-block">
                Mulai Belanja
            </a>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Cart Items -->
            <div class="md:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                    <div class="card flex gap-4">
                        <div class="w-20 h-20 bg-background rounded-lg flex items-center justify-center text-2xl flex-shrink-0">
                            📦
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium">{{ $item['name'] }}</h3>
                            <p class="text-primary font-semibold">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            
                            <div class="flex items-center justify-between mt-2">
                                <!-- Quantity Control -->
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-background flex items-center justify-center hover:bg-border">-</button>
                                    </form>
                                    <span class="w-8 text-center font-medium">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-background flex items-center justify-center hover:bg-border">+</button>
                                    </form>
                                </div>

                                <!-- Remove -->
                                <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-error hover:underline text-sm">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="card h-fit sticky top-24">
                <h2 class="font-bold text-lg mb-4">Ringkasan Pesanan</h2>
                
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-text-secondary">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-text-secondary">
                        <span>Ongkir</span>
                        <span class="text-secondary font-medium">GRATIS</span>
                    </div>
                    <hr class="border-border">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" class="btn-primary block text-center">
                    Checkout
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
