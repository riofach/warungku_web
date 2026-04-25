import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('cart', {
    count: 0,
    animating: false,

    init() {
        // Initial count injected via server-side x-init
    },

    updateCount(newCount) {
        this.count = newCount;
        this.triggerAnimation();
    },

    triggerAnimation() {
        this.animating = true;
        setTimeout(() => this.animating = false, 300);
    },

    async fetchCount() {
        try {
            const response = await fetch('/cart/count');
            if (response.ok) {
                const data = await response.json();
                this.updateCount(data.count);
            }
        } catch (error) {
            console.error('Error fetching cart count:', error);
        }
    },

    /** Add regular (non-unit) item */
    async add(itemId) {
        await this._addToCart({ item_id: itemId, quantity: 1 });
    },

    /** Add unit item (has_units=true) */
    async addWithUnit(itemId, itemUnitId) {
        await this._addToCart({ item_id: itemId, quantity: 1, item_unit_id: itemUnitId });
    },

    async _addToCart(payload) {
        const previousCount = this.count;
        this.count++;
        this.triggerAnimation();

        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: 'Menambahkan ke keranjang...', type: 'info' }
        }));

        try {
            const token    = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch('/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.updateCount(data.cart_count);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message, type: 'success' }
                }));
            } else {
                this.count = previousCount;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message || 'Gagal menambahkan item', type: 'error' }
                }));
            }
        } catch (error) {
            this.count = previousCount;
            console.error('Error adding to cart:', error);
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message: 'Terjadi kesalahan sistem', type: 'error' }
            }));
        }
    },
});

Alpine.start();
