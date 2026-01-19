<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WarungKu - Belanja mudah dari warung tetangga">
    <title>@yield('title', 'WarungKu') - Belanja Mudah</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏪</text></svg>">
    
    <!-- Inter Font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-background text-text-primary font-sans min-h-screen flex flex-col" x-data>
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-surface shadow-sm border-b border-border">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('shop.index') }}" class="flex items-center gap-2">
                    <span class="text-2xl">🏪</span>
                    <span class="text-xl font-bold text-primary">WarungKu</span>
                </a>

                <!-- Search (Desktop) -->
                <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Cari produk..."
                            value="{{ request('search') }}"
                            class="input-field pl-10 pr-4 py-2"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>

                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="relative p-2 hover:bg-background rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <!-- Cart Badge -->
                    <span 
                        x-show="$store.cart.count > 0"
                        x-text="$store.cart.count"
                        class="absolute -top-1 -right-1 bg-primary text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-cart-bounce"
                    ></span>
                </a>
            </div>

            <!-- Search (Mobile) -->
            <div class="md:hidden pb-3">
                <form action="{{ route('shop.index') }}" method="GET">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Cari produk..."
                            value="{{ request('search') }}"
                            class="input-field pl-10 pr-4 py-2 text-sm"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container mx-auto px-4 pt-4">
                <div class="bg-success/10 border border-success text-success px-4 py-3 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto px-4 pt-4">
                <div class="bg-error/10 border border-error text-error px-4 py-3 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="md:hidden sticky bottom-0 bg-surface border-t border-border safe-area-inset-bottom">
        <div class="grid grid-cols-3 gap-1 p-2">
            <a href="{{ route('shop.index') }}" class="flex flex-col items-center py-2 px-4 rounded-lg {{ request()->routeIs('shop.*') ? 'text-primary bg-primary/10' : 'text-text-secondary hover:bg-background' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-xs mt-1 font-medium">Beranda</span>
            </a>
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center py-2 px-4 rounded-lg relative {{ request()->routeIs('cart.*') ? 'text-primary bg-primary/10' : 'text-text-secondary hover:bg-background' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-xs mt-1 font-medium">Keranjang</span>
                <span 
                    x-show="$store.cart.count > 0"
                    x-text="$store.cart.count"
                    class="absolute top-1 right-1/4 bg-primary text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center"
                ></span>
            </a>
            <a href="{{ route('tracking.index') }}" class="flex flex-col items-center py-2 px-4 rounded-lg {{ request()->routeIs('tracking.*') ? 'text-primary bg-primary/10' : 'text-text-secondary hover:bg-background' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs mt-1 font-medium">Pesanan</span>
            </a>
        </div>
    </nav>

    <!-- Footer (Desktop) -->
    <footer class="hidden md:block bg-surface border-t border-border mt-8">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏪</span>
                    <span class="font-bold text-primary">WarungKu</span>
                </div>
                <p class="text-text-secondary text-sm">
                    &copy; {{ date('Y') }} WarungKu Digital. Dibuat dengan ❤️
                </p>
                <div class="flex gap-4">
                    <a href="{{ route('tracking.index') }}" class="text-text-secondary hover:text-primary text-sm">
                        Lacak Pesanan
                    </a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
