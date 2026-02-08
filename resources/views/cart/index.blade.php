@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="cartPage()">
    <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

    @if(empty($cartItems))
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-xl font-semibold mb-2">Keranjang kosong</h2>
            <p class="text-text-secondary mb-4">Belum ada produk di keranjang Anda</p>
            <a href="{{ route('home') }}" class="btn-primary inline-block">
                Mulai Belanja
            </a>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-6" x-show="cartItems.length > 0">
            <!-- Cart Items -->
            <div class="md:col-span-2 space-y-4">
                <template x-for="(item, index) in cartItems" :key="item.id">
                    <div class="card flex gap-4 transition-all duration-300" :id="'item-' + item.id">
                        <!-- Image -->
                        <div class="w-20 h-20 bg-background rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 relative">
                             <template x-if="item.image_url">
                                <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                             </template>
                             <template x-if="!item.image_url">
                                <span class="text-2xl">📦</span>
                             </template>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-medium text-lg" x-text="item.name"></h3>
                                <p class="text-primary font-semibold" x-text="formatRupiah(item.price)"></p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-3">
                                <!-- Quantity Control -->
                                <div class="flex items-center gap-1 bg-background rounded-lg p-1 border border-border w-fit">
                                    <button 
                                        @click="changeQuantity(item, -1)" 
                                        class="w-8 h-8 rounded-md hover:bg-gray-200 flex items-center justify-center text-lg font-bold transition-colors disabled:opacity-50"
                                        :disabled="item.loading || item.quantity <= 1"
                                    >-</button>
                                    
                                    <input 
                                        type="number" 
                                        x-model.number="item.quantity"
                                        @input="handleInput(item)"
                                        @input.debounce.500ms="saveQuantity(item)"
                                        @keyup.enter="$el.blur()"
                                        class="w-12 text-center font-medium bg-transparent border-none p-0 focus:ring-0 appearance-none [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none"
                                        min="1"
                                        :max="item.stock_max"
                                        :disabled="item.loading"
                                    >
                                    
                                    <button 
                                        @click="changeQuantity(item, 1)" 
                                        class="w-8 h-8 rounded-md hover:bg-gray-200 flex items-center justify-center text-lg font-bold transition-colors disabled:opacity-50"
                                        :disabled="item.loading || (item.stock_max && item.quantity >= item.stock_max)"
                                    >+</button>
                                </div>

                                <!-- Remove -->
                                <button 
                                    @click="confirmDelete(item)" 
                                    class="text-error hover:text-red-700 text-sm font-medium flex items-center gap-1 transition-colors"
                                    :disabled="item.loading"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Subtotal -->
                        <div class="text-right hidden sm:block">
                            <span class="font-bold text-lg" x-text="formatRupiah(item.price * item.quantity)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Order Summary -->
            <div class="h-fit sticky top-24">
                <div class="card p-6">
                    <h2 class="font-bold text-lg mb-4 pb-2 border-b border-border">Ringkasan Pesanan</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-text-secondary">
                            <span>Total Item</span>
                            <span x-text="cartCount + ' barang'"></span>
                        </div>
                        <div class="flex justify-between text-text-secondary">
                            <span>Subtotal</span>
                            <span x-text="cartTotalFormatted"></span>
                        </div>
                        <div class="flex justify-between text-text-secondary">
                            <span>Ongkir</span>
                            <span class="text-success font-medium">GRATIS</span>
                        </div>
                        <div class="h-px bg-border my-2"></div>
                        <div class="flex justify-between font-bold text-xl">
                            <span>Total</span>
                            <span class="text-primary" x-text="cartTotalFormatted"></span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn-primary block text-center w-full py-3 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all">
                        Lanjut ke Checkout
                    </a>
                    
                    <a href="{{ route('home') }}" class="block text-center mt-4 text-sm text-text-secondary hover:text-primary transition-colors">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

        <!-- Empty State (Hidden by default, shown via Alpine) -->
        <div x-show="cartItems.length === 0" class="text-center py-20" style="display: none;">
            <div class="text-7xl mb-6 animate-bounce">🛒</div>
            <h2 class="text-2xl font-bold mb-3">Keranjang kosong</h2>
            <p class="text-text-secondary mb-8">Belum ada produk di keranjang Anda</p>
            <a href="{{ route('home') }}" class="btn-primary inline-flex items-center gap-2 px-8 py-3 rounded-full">
                <span>Mulai Belanja</span>
            </a>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div
        x-show="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
    >
        <div 
            class="bg-surface rounded-xl shadow-xl w-full max-w-sm p-6"
            @click.outside="showDeleteModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="text-center">
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-text-primary mb-2">Hapus Item?</h3>
                <p class="text-text-secondary text-sm mb-6">
                    Apakah Anda yakin ingin menghapus <span class="font-bold text-text-primary" x-text="itemToDelete?.name"></span> dari keranjang?
                </p>
                <div class="flex gap-3 justify-center">
                    <button 
                        @click="showDeleteModal = false" 
                        class="px-4 py-2 rounded-lg border border-border text-text-secondary font-medium hover:bg-background transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        @click="removeItem()" 
                        class="px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartPage', () => ({
            // Initialize items with explicit loading state and ensure array format
            cartItems: @json(array_values($cartItems)).map(item => ({ ...item, loading: false })),
            cartTotal: {{ $total }},
            showDeleteModal: false,
            itemToDelete: null,
            
            get cartCount() {
                return this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
            },

            get cartTotalFormatted() {
                return this.formatRupiah(this.cartTotal);
            },

            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            },

            // Updates local total based on current array state (instant)
            calculateLocalTotal() {
                this.cartTotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            // Called by +/- buttons
            changeQuantity(item, change) {
                const newQty = parseInt(item.quantity) + change;
                if (newQty < 1) return;
                
                if (item.stock_max && newQty > item.stock_max) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Stok tidak mencukupi (Max: ' + item.stock_max + ')', type: 'error' }
                    }));
                    return;
                }

                item.quantity = newQty;
                this.calculateLocalTotal();
                this.saveQuantity(item); // Immediate save for clicks
            },

            // Called by input @input (instant UI update)
            handleInput(item) {
                // If empty or invalid, don't break UI, just wait
                if (!item.quantity || item.quantity < 1) {
                     // Optionally keep '1' or wait for blur? 
                     // Let's allow typing but min 1 for calc
                     // Actually better not to mutate aggressively while typing
                }
                
                // If user exceeds max stock while typing
                if (item.stock_max && item.quantity > item.stock_max) {
                    // We don't block typing but show toast? Or just clamp?
                    // Clamping while typing is annoying. Let's just calc what we have.
                    // Validation happens on save.
                }

                this.calculateLocalTotal();
            },

            // Called by input @input.debounce (server update)
            async saveQuantity(item) {
                // Final validation before sending
                if (!item.quantity || item.quantity < 1) {
                    item.quantity = 1;
                    this.calculateLocalTotal();
                }
                if (item.stock_max && item.quantity > item.stock_max) {
                     item.quantity = item.stock_max;
                     this.calculateLocalTotal();
                     window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Stok disesuaikan ke maksimum (' + item.stock_max + ')', type: 'warning' }
                    }));
                }

                item.loading = true;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/cart/${item.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: item.quantity })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.message || 'Gagal update');
                    }

                    // Sync server total just in case
                    this.cartTotal = data.cart_total;
                    
                    // Update header badge
                    if (this.$store.cart) {
                        this.$store.cart.updateCount(data.cart_count);
                    }

                } catch (error) {
                    console.error('Update quantity error:', error);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: error.message || 'Gagal menyimpan perubahan', type: 'error' }
                    }));
                    // Don't revert blindly as user might have typed more? 
                    // But for consistency we might want to reload or revert to prev?
                    // For now, error toast is enough.
                } finally {
                    item.loading = false;
                }
            },

            confirmDelete(item) {
                this.itemToDelete = item;
                this.showDeleteModal = true;
            },

            async removeItem() {
                if (!this.itemToDelete) return;
                
                const item = this.itemToDelete;
                const itemId = item.id;
                const itemIndex = this.cartItems.findIndex(i => i.id === itemId);
                
                if (itemIndex === -1) return;

                item.loading = true;
                this.showDeleteModal = false; // Close modal immediately or wait? Better close to show progress on item

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/cart/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });

                    // Check if response is ok before parsing JSON (in case of 404/500 HTML response)
                    if (!response.ok) {
                        throw new Error(`Gagal menghubungi server (${response.status})`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.cartItems.splice(itemIndex, 1);
                        this.cartTotal = data.cart_total;
                        
                        if (this.$store.cart) {
                            this.$store.cart.count = data.cart_count;
                        }

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Item berhasil dihapus', type: 'success' }
                        }));
                    } else {
                        throw new Error(data.message || 'Gagal menghapus item');
                    }
                } catch (error) {
                    console.error('Remove item error:', error);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: error.message || 'Gagal menghapus item', type: 'error' }
                    }));
                } finally {
                    // Reset loading state if item still exists (failed request)
                    // If spliced, this item reference is detached from array so it doesn't matter,
                    // but if it wasn't spliced (failure), we MUST reset it.
                     if (this.cartItems[itemIndex] === item) {
                         item.loading = false;
                     }
                     this.itemToDelete = null;
                }
            }
        }));
    });
</script>
@endpush
@endsection
