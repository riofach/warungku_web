@extends('layouts.app')

@section('title', 'Belanja')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-6 md:p-8 mb-6 text-white">
        <h1 class="text-2xl md:text-3xl font-bold mb-2">Selamat datang di WarungKu! 👋</h1>
        <p class="text-white/80 text-sm md:text-base">Belanja kebutuhan harian Anda dengan mudah. Gratis ongkir ke seluruh perumahan!</p>
        <div class="flex items-center gap-2 mt-4">
            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium">🚚 FREE DELIVERY</span>
            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium">💳 QRIS & Tunai</span>
        </div>
    </div>

    <!-- Category Pills -->
    <div class="mb-6 overflow-x-auto scrollbar-hide">
        <div class="flex gap-2 pb-2">
            <a href="{{ route('shop.index') }}" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ !request('category') ? 'bg-primary text-white' : 'bg-surface border border-border text-text-secondary hover:border-primary' }}">
                Semua
            </a>
            @foreach($categories as $category)
                <a href="{{ route('shop.index', ['category' => $category->id]) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ request('category') == $category->id ? 'bg-primary text-white' : 'bg-surface border border-border text-text-secondary hover:border-primary' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Products Grid -->
    @if($items->isEmpty())
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🔍</div>
            <h2 class="text-xl font-semibold mb-2">Produk tidak ditemukan</h2>
            <p class="text-text-secondary">Coba kata kunci lain atau lihat semua produk</p>
            <a href="{{ route('shop.index') }}" class="btn-primary inline-block mt-4">
                Lihat Semua Produk
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($items as $item)
                <div class="card group hover:shadow-md transition-shadow">
                    <!-- Product Image -->
                    <div class="aspect-square bg-background rounded-lg mb-3 overflow-hidden">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-4xl">
                                📦
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <h3 class="font-medium text-sm mb-1 line-clamp-2">{{ $item->name }}</h3>
                    
                    @if($item->category)
                        <span class="text-xs text-text-tertiary">{{ $item->category->name }}</span>
                    @endif

                    <div class="flex items-center justify-between mt-2">
                        <span class="font-bold text-primary">{{ $item->formatted_price }}</span>
                        
                        <!-- Stock Indicator -->
                        @if($item->isOutOfStock())
                            <span class="text-xs text-stock-critical font-medium">Habis</span>
                        @elseif($item->isStockLow())
                            <span class="text-xs text-stock-warning font-medium">Sisa {{ $item->stock }}</span>
                        @endif
                    </div>

                    <!-- Add to Cart Button -->
                    <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <button 
                            type="submit" 
                            @if($item->isOutOfStock()) disabled @endif
                            class="w-full py-2 rounded-lg text-sm font-semibold transition-colors {{ $item->isOutOfStock() ? 'bg-background text-text-tertiary cursor-not-allowed' : 'bg-primary text-white hover:bg-primary-dark' }}"
                        >
                            @if($item->isOutOfStock())
                                Stok Habis
                            @else
                                + Keranjang
                            @endif
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
