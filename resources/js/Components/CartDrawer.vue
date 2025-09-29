<!-- resources/js/Components/CartDrawer.vue -->
<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/stores/cart';
import { useCafeStore } from '@/stores/cafe';
import { euro } from '@/utils/money';

const open = ref(false);
const paying = ref(false);
const placing = ref(false);
const placedOrder = ref(null); // store order after checkout

const cart = useCartStore(); cart.hydrate();
const cafe = useCafeStore(); cafe.hydrate();

const subtotal = computed(() => cart.subtotalCents);
const prefs = [
  { v:'cash', label:'Cash' },
  { v:'card', label:'Card' },
  { v:'online', label:'Online' },
];

const form = ref({
  payment_preference: 'cash',
  table_number: '',
});

async function checkout() {
  if (!cafe.cafe) return alert('Choose a café first.');
  if (cart.items.length === 0) return;

  placing.value = true;
  try {
    const payload = {
      cafe_id: cafe.cafe.id,
      branch_id: cafe.branch?.id ?? null,
      items: cart.items.map(it => ({ product_id: it.product_id, quantity: it.qty })),
      payment_preference: form.value.payment_preference || null,
      table_number: form.value.table_number ? parseInt(form.value.table_number, 10) : null,
    };
    const res = await axios.post('/api/orders', payload);
    placedOrder.value = res.data; // assuming resource returns flat fields
    cart.clear();
  } catch (e) {
    console.error(e);
    alert(e.response?.data?.message || 'Order failed.');
  } finally {
    placing.value = false;
  }
}

async function payMock() {
  if (!placedOrder.value?.id) return;
  paying.value = true;
  try {
    const res = await axios.post(`/api/orders/${placedOrder.value.id}/pay-mock`);
    placedOrder.value = res.data.order ?? res.data; // controller returns {payment, order}
  } catch (e) {
    console.error(e);
    alert(e.response?.data?.message || 'Payment failed.');
  } finally {
    paying.value = false;
  }
}
</script>

<template>
  <!-- Floating Cart Button -->
  <button
    class="fixed bottom-6 right-6 btn btn-primary shadow-soft"
    @click="open = true"
    aria-label="Open cart"
  >
    🛒 Cart <span v-if="cart.count" class="ml-2 badge">{{ cart.count }}</span>
  </button>

  <!-- Drawer -->
  <div v-if="open" class="fixed inset-0 z-40">
    <div class="absolute inset-0 bg-black/20" @click="open = false"></div>

    <div class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white border-l border-brand-100 shadow-soft p-5 overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-brand-700">Your Cart</h2>
        <button class="btn btn-ghost" @click="open = false">✕</button>
      </div>

      <!-- After order placed -->
      <div v-if="placedOrder" class="card mb-4">
        <div class="card-body">
          <h3 class="font-semibold mb-1">Order #{{ placedOrder.id }}</h3>
          <p class="text-sm text-ink-900/70 mb-3">
            Total: <strong>€{{ placedOrder.total_price }}</strong> · Status: <strong>{{ placedOrder.status }}</strong>
            <template v-if="placedOrder.payment_status"> · Payment: <strong>{{ placedOrder.payment_status }}</strong></template>
          </p>
          <div class="flex gap-2">
            <button class="btn btn-primary" :disabled="paying || placedOrder.payment_status==='paid'" @click="payMock">
              <span v-if="!paying">Pay (mock)</span><span v-else>Processing…</span>
            </button>
            <button class="btn btn-secondary" @click="placedOrder=null; open=false">Close</button>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="!placedOrder && cart.items.length===0" class="text-sm text-ink-900/70">
        Your cart is empty.
      </div>

      <!-- Items -->
      <div v-if="!placedOrder && cart.items.length">
        <ul class="space-y-3 mb-4">
          <li v-for="it in cart.items" :key="it.product_id" class="flex items-center gap-3">
            <div class="flex-1">
              <div class="font-medium">{{ it.name }}</div>
              <div class="text-xs text-ink-900/60">{{ euro(it.price_cents) }} · item</div>
            </div>
            <div class="flex items-center gap-2">
              <button class="btn btn-ghost" @click="cart.dec(it.product_id)">−</button>
              <input class="input w-16 text-center" :value="it.qty" @input="e => cart.setQty(it.product_id, e.target.value)">
              <button class="btn btn-ghost" @click="cart.inc(it.product_id)">+</button>
            </div>
            <button class="btn btn-ghost" @click="cart.remove(it.product_id)">🗑</button>
          </li>
        </ul>

        <div class="border-t hr my-4"></div>

        <div class="flex items-center justify-between mb-4">
          <div class="text-sm text-ink-900/70">Subtotal</div>
          <div class="font-semibold">{{ euro(subtotal) }}</div>
        </div>

        <!-- Checkout form -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="col-span-2">
            <label class="label">Payment preference</label>
            <select v-model="form.payment_preference" class="select">
              <option v-for="o in prefs" :key="o.v" :value="o.v">{{ o.label }}</option>
            </select>
          </div>
          <div>
            <label class="label">Table</label>
            <input v-model="form.table_number" type="number" min="1" class="input" placeholder="(optional)">
          </div>
          <div>
            <label class="label">Branch</label>
            <div class="text-sm text-ink-900/70">
              <span v-if="cafe.branch">{{ cafe.branch.name }}</span>
              <span v-else>-</span>
            </div>
          </div>
        </div>

        <button class="btn btn-primary w-full" :disabled="placing || !cafe.cafe" @click="checkout">
          <span v-if="!placing">Place order</span><span v-else>Placing…</span>
        </button>

        <button class="btn btn-secondary w-full mt-2" @click="cart.clear">Clear cart</button>
      </div>
    </div>
  </div>
</template>

