@props(['item'])

<div class="card group relative bg-surface rounded-xl shadow-sm border border-border overflow-hidden hover:shadow-md transition-shadow h-full flex flex-col">
    <!-- Image Container -->
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
        @if($item->stock == 0)
            <div class="absolute inset-0 bg-black/50 z-20 flex items-center justify-center backdrop-blur-[2px]">
                <div class="bg-red-600/90 text-white px-4 py-1.5 text-sm font-bold rounded-full shadow-lg transform -rotate-12 border-2 border-white/50">
                    HABIS
                </div>
            </div>
        @endif

        @if($item->stock > 0 && $item->stock < 5)
            <span class="absolute top-2 right-2 z-10 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                Sisa {{ $item->stock }}
            </span>
        @endif

        @if($item->image_url)
            <img 
                src="{{ $item->image_url }}" 
                alt="{{ $item->name }}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $item->stock == 0 ? 'grayscale' : '' }}"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-4xl bg-gray-50 {{ $item->stock == 0 ? 'grayscale' : '' }}">
                📦
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-3 flex flex-col flex-1">
        <h3 class="font-medium text-sm text-text-primary line-clamp-2 mb-1 min-h-[2.5em]">{{ $item->name }}</h3>
        
        <div class="mt-auto">
            <div class="font-bold text-primary mb-3">
                Rp {{ number_format($item->sell_price, 0, ',', '.') }}
            </div>

            <button 
                class="w-full py-2 px-4 rounded-lg text-sm font-semibold transition-colors 
                {{ $item->stock == 0 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-primary text-white hover:bg-blue-700 active:bg-blue-800 cursor-pointer' }}"
                {{ $item->stock == 0 ? 'disabled' : '' }}
                onclick="alert('Fitur keranjang akan hadir di update berikutnya!')" 
            >
                Beli
            </button>
        </div>
    </div>
</div>
