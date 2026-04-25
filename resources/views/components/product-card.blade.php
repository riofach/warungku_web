@props(['item'])

@php
    $outOfStock = $item->has_units
        ? $item->activeUnits->every(fn($u) => $item->availableForUnit($u->quantity_base) === 0)
        : $item->stock === 0;
    $lowStock = !$item->has_units && $item->stock > 0 && $item->stock < 5;
@endphp

<div class="card group relative bg-surface rounded-xl shadow-sm border border-border overflow-hidden hover:shadow-md transition-shadow h-full flex flex-col isolate">
    <!-- Image Container -->
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
        @if($outOfStock)
            <div class="absolute inset-0 bg-black/50 z-20 flex items-center justify-center backdrop-blur-[2px]">
                <div class="bg-red-600/90 text-white px-4 py-1.5 text-sm font-bold rounded-full shadow-lg transform -rotate-12 border-2 border-white/50">
                    HABIS
                </div>
            </div>
        @endif

        @if($lowStock)
            <span class="absolute top-2 right-2 z-10 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                Sisa {{ $item->stock }}
            </span>
        @endif

        @if($item->image_url)
            <img
                src="{{ $item->image_url }}"
                alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $outOfStock ? 'grayscale' : '' }}"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-4xl bg-gray-50 {{ $outOfStock ? 'grayscale' : '' }}">
                📦
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-3 flex flex-col flex-1">
        <h3 class="font-medium text-sm text-text-primary line-clamp-2 mb-1 min-h-[2.5em]">{{ $item->name }}</h3>

        <div class="mt-auto">
            {{-- Price display --}}
            <div class="font-bold text-primary mb-3">
                @if($item->has_units && $item->activeUnits->isNotEmpty())
                    <span class="text-xs font-normal text-text-secondary">Mulai dari </span>
                    {{ \App\Helpers\FormatHelper::rupiah($item->activeUnits->min('sell_price')) }}
                @else
                    {{ \App\Helpers\FormatHelper::rupiah($item->sell_price) }}
                @endif
            </div>

            @if($item->has_units)
                @php
                    $unitPickerData = [
                        'id'       => $item->id,
                        'name'     => $item->name,
                        'imageUrl' => $item->image_url,
                        'units'    => $item->activeUnits->map(function ($u) use ($item) {
                            return [
                                'id'         => $u->id,
                                'label'      => $u->label,
                                'sell_price' => $u->sell_price,
                                'available'  => $item->availableForUnit($u->quantity_base),
                            ];
                        })->values()->toArray(),
                    ];
                @endphp
                {{-- Multi-unit: open picker modal --}}
                <button
                    class="w-full py-2 px-4 rounded-lg text-sm font-semibold transition-colors
                    {{ $outOfStock
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-primary text-white hover:bg-blue-700 active:bg-blue-800 cursor-pointer' }}"
                    {{ $outOfStock ? 'disabled' : '' }}
                    @if(!$outOfStock)
                        @click="$dispatch('open-unit-picker', {{ Js::from($unitPickerData) }})"
                    @endif
                >
                    Pilih Satuan
                </button>
            @else
                {{-- Regular item: add directly --}}
                <button
                    class="w-full py-2 px-4 rounded-lg text-sm font-semibold transition-colors
                    {{ $outOfStock
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-primary text-white hover:bg-blue-700 active:bg-blue-800 cursor-pointer' }}"
                    {{ $outOfStock ? 'disabled' : '' }}
                    @if(!$outOfStock)
                        x-data
                        @click="$store.cart.add('{{ $item->id }}')"
                    @endif
                >
                    Beli
                </button>
            @endif
        </div>
    </div>
</div>
