{{-- Unit Picker Modal: muncul untuk item has_units saat klik "Pilih Satuan" --}}
<div
    x-data="unitPickerModal()"
    x-show="open"
    @open-unit-picker.window="openWith($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display:none;"
>
    <div
        class="bg-surface rounded-2xl shadow-xl w-full max-w-sm"
        @click.outside="close()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
    >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-border">
            <div>
                <p class="text-xs text-text-secondary mb-0.5">Pilih Satuan</p>
                <h3 class="font-bold text-text-primary" x-text="item.name"></h3>
            </div>
            <button @click="close()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-background transition-colors text-text-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Unit Options -->
        <div class="p-4 space-y-2">
            <template x-for="unit in item.units" :key="unit.id">
                <button
                    @click="selectUnit(unit)"
                    :disabled="unit.available === 0 || loading"
                    class="w-full flex items-center justify-between p-3 rounded-xl border-2 transition-all text-left"
                    :class="unit.available === 0
                        ? 'border-border bg-background text-text-secondary cursor-not-allowed opacity-60'
                        : 'border-border hover:border-primary hover:bg-primary/5 cursor-pointer'"
                >
                    <div>
                        <span class="font-semibold text-sm" x-text="unit.label"></span>
                        <span
                            class="ml-2 text-xs"
                            :class="unit.available === 0 ? 'text-red-400' : 'text-text-secondary'"
                            x-text="unit.available === 0 ? 'Habis' : ('Sisa ' + unit.available)"
                        ></span>
                    </div>
                    <span class="font-bold text-primary text-sm" x-text="formatRupiah(unit.sell_price)"></span>
                </button>
            </template>
        </div>

        <!-- Loading indicator -->
        <div x-show="loading" class="px-4 pb-4 text-center text-sm text-text-secondary">
            Menambahkan...
        </div>
    </div>
</div>

@push('scripts')
<script>
    function unitPickerModal() {
        return {
            open: false,
            loading: false,
            item: { id: '', name: '', units: [] },

            openWith(detail) {
                this.item    = detail;
                this.loading = false;
                this.open    = true;
            },

            close() {
                if (this.loading) return;
                this.open = false;
            },

            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            },

            async selectUnit(unit) {
                if (unit.available === 0 || this.loading) return;
                this.loading = true;

                try {
                    await this.$store.cart.addWithUnit(this.item.id, unit.id);
                    this.open = false;
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
