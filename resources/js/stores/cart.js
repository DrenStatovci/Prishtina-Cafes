// resources/js/stores/cart.js
import { defineStore } from 'pinia';
const KEY = 'cart_v1';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [] // [{ product_id, name, price_cents, qty, image_url? }]
    }),
    getters: {
        count: (s) => s.items.reduce((n, it) => n + it.qty, 0),
        subtotalCents: (s) => s.items.reduce((sum, it) => sum + it.price_cents * it.qty, 0),
    },
    actions: {
        hydrate() {
            try { const raw = localStorage.getItem(KEY); if (raw) this.items = JSON.parse(raw); } catch { }
        },
        persist() { localStorage.setItem(KEY, JSON.stringify(this.items)); },
        add(p, qty = 1, price_cents) {
            const id = p.id ?? p.product_id;
            const i = this.items.findIndex(x => x.product_id === id);
            if (i === -1) {
                this.items.push({
                    product_id: id,
                    name: p.name,
                    price_cents,
                    qty: qty,
                    image_url: p.image_url ?? null,
                });
            } else {
                this.items[i].qty = Math.min(this.items[i].qty + qty, 50);
            }
            this.persist();
        },
        setQty(product_id, qty) {
            const it = this.items.find(x => x.product_id === product_id);
            if (!it) return;
            it.qty = Math.max(1, Math.min(50, parseInt(qty || 1, 10)));
            this.persist();
        },
        inc(product_id) {
            const it = this.items.find(x => x.product_id === product_id);
            if (!it) return; it.qty = Math.min(50, it.qty + 1); this.persist();
        },
        dec(product_id) {
            const it = this.items.find(x => x.product_id === product_id);
            if (!it) return; it.qty = Math.max(1, it.qty - 1); this.persist();
        },
        remove(product_id) {
            this.items = this.items.filter(x => x.product_id !== product_id);
            this.persist();
        },
        clear() { this.items = []; this.persist(); },
    },
});
