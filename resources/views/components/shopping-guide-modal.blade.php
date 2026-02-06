<div x-data="{
        show: false,
        dontShowAgain: false,
        init() {
            // Check if guide has been seen
            if (!localStorage.getItem('warungku_guide_seen')) {
                // Delay slightly for better UX
                setTimeout(() => this.show = true, 500);
            }
        },
        close() {
            if (this.dontShowAgain) {
                localStorage.setItem('warungku_guide_seen', 'true');
            }
            this.show = false;
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[60] flex items-center justify-center px-4 sm:px-0"
    style="display: none;"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="guide-title"
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

    <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 sm:p-8 transform transition-all"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    >
        <!-- Header -->
        <div class="text-center mb-6">
            <h2 id="guide-title" class="text-2xl font-bold text-gray-900 mb-2">Cara Belanja di Warung Luthfan</h2>
            <p class="text-gray-600 text-sm">Belanja mudah tanpa ribet, langsung dari rumah!</p>
        </div>

        <!-- Steps -->
        <div class="space-y-4 mb-8">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Pilih Barang</h3>
                    <p class="text-xs text-gray-500">Cari dan masukkan barang ke keranjang.</p>
                </div>
            </div>
            
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Keranjang</h3>
                    <p class="text-xs text-gray-500">Cek kembali belanjaan kamu.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Checkout</h3>
                    <p class="text-xs text-gray-500">Isi nama dan pilih blok rumah kamu.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    4
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Bayar</h3>
                    <p class="text-xs text-gray-500">Pilih Tunai atau Scan QRIS.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold text-sm">
                    5
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Tunggu</h3>
                    <p class="text-xs text-gray-500">Pesanan akan segera diantar ke rumahmu!</p>
                </div>
            </div>
        </div>

        <!-- Footer / Actions -->
        <div>
            <button @click="close()" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-lg hover:shadow-xl mb-4 text-sm">
                Mulai Belanja
            </button>

            <div class="flex items-center justify-center gap-2">
                <input type="checkbox" id="dontShow" x-model="dontShowAgain" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                <label for="dontShow" class="text-xs text-gray-500 select-none cursor-pointer">
                    Jangan tampilkan lagi
                </label>
            </div>
        </div>
    </div>
</div>
