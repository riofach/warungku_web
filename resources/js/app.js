import './bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;

// Alpine.js stores for cart management
Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('warungku_cart') || '[]'),
    
    get count() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    },
    
    get total() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },
    
    add(item) {
        const existingIndex = this.items.findIndex(i => i.id === item.id);
        if (existingIndex > -1) {
            this.items[existingIndex].quantity += 1;
        } else {
            this.items.push({ ...item, quantity: 1 });
        }
        this.save();
    },
    
    remove(itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.save();
    },
    
    updateQuantity(itemId, quantity) {
        const item = this.items.find(i => i.id === itemId);
        if (item) {
            item.quantity = Math.max(1, quantity);
            this.save();
        }
    },
    
    clear() {
        this.items = [];
        this.save();
    },
    
    save() {
        localStorage.setItem('warungku_cart', JSON.stringify(this.items));
    }
});

// Start Alpine
Alpine.start();
