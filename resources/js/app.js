import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('cart', {
    count: 0,
    animating: false,
    
    init() {
        // Initial count is injected via server-side x-init
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
    
    async add(itemId) {
        // Optimistic update
        const previousCount = this.count;
        this.count++;
        this.triggerAnimation();
        
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: 'Menambahkan ke keranjang...', type: 'info' }
        }));

        try {
            // Get token manually or ensure it exists
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            
            const response = await fetch('/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    item_id: itemId,
                    quantity: 1
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Sync with server count just in case
                this.updateCount(data.cart_count);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message, type: 'success' }
                }));
            } else {
                // Revert optimistic update
                this.count = previousCount;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message || 'Gagal menambahkan item', type: 'error' }
                }));
            }
        } catch (error) {
            // Revert optimistic update
            this.count = previousCount;
            console.error('Error adding to cart:', error);
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message: 'Terjadi kesalahan sistem', type: 'error' }
            }));
        }
    }
});

Alpine.start();
