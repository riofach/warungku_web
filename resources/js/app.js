import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('cart', {
    count: 0,
    
    init() {
        this.fetchCount();
    },
    
    async fetchCount() {
        try {
            const response = await fetch('/cart/count');
            if (response.ok) {
                const data = await response.json();
                this.count = data.count;
            }
        } catch (error) {
            console.error('Error fetching cart count:', error);
        }
    },
    
    async add(itemId) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
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
                this.count = data.cart_count;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message, type: 'success' }
                }));
            } else {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: data.message || 'Gagal menambahkan item', type: 'error' }
                }));
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message: 'Terjadi kesalahan sistem', type: 'error' }
            }));
        }
    }
});

Alpine.start();
