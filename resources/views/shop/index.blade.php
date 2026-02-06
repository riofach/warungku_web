@extends('layouts.app')

@section('title', 'Belanja')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary to-blue-700 rounded-2xl p-6 md:p-8 mb-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <h1 class="text-2xl md:text-3xl font-bold mb-2">Selamat datang di WarungLuthfan! 👋</h1>
            <p class="text-white/90 text-sm md:text-base mb-4">Belanja kebutuhan harian Anda dengan mudah.</p>
            <div class="flex flex-wrap items-center gap-2">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs md:text-sm font-medium flex items-center gap-1">
                    🚚 Gratis Ongkir
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs md:text-sm font-medium flex items-center gap-1">
                    ⚡ Proses Cepat
                </span>
            </div>
        </div>
        <!-- Decorative Circle -->
        <div class="absolute -right-8 -bottom-16 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
    </div>

    <!-- Category Section -->
    <div class="mb-6">
        <div class="flex gap-2 overflow-x-auto pb-4 scrollbar-hide -mx-4 px-4 md:mx-0 md:px-0">
            <a href="{{ route('home') }}" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all border
               {{ !request('category') 
                   ? 'bg-primary border-primary text-white shadow-md' 
                   : 'bg-white border-gray-200 text-gray-600 hover:border-primary hover:text-primary' }}">
                Semua
            </a>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->id]) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all border
                   {{ request('category') == $category->id 
                       ? 'bg-primary border-primary text-white shadow-md' 
                       : 'bg-white border-gray-200 text-gray-600 hover:border-primary hover:text-primary' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Product Grid -->
    @if($items->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center text-4xl mb-4">
                🔍
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Produk tidak ditemukan</h2>
            <p class="text-gray-500 max-w-xs mx-auto mb-6">Maaf, kami tidak dapat menemukan produk yang Anda cari.</p>
            <a href="{{ route('home') }}" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Lihat Semua Produk
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($items as $item)
                <x-product-card :item="$item" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $items->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
