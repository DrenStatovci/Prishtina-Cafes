<!-- resources/js/Components/ProductsGrid.vue -->
<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/stores/cart';
import { toCents } from '@/utils/money';

const cart = useCartStore(); cart.hydrate();
const props = defineProps({ categorySlug: { type: String, default: null } });
const products = ref([]), meta = ref(null), loading = ref(false);
const search = ref(''), sort = ref('created_at'), dir = ref('desc'), page = ref(1);

function addToCart(p) {
  const cents = toCents(p.price);
  cart.add(p, 1, cents);
}

const getPaginated = (res) => {
  const body = res.data || {};
  return { items: Array.isArray(body) ? body : (Array.isArray(body.data) ? body.data : []), meta: body.meta || null };
};
async function fetchProducts() {
  if (!props.categorySlug) { products.value=[]; meta.value=null; return; }
  loading.value = true;
  try {
    const res = await axios.get(`/api/categories/${props.categorySlug}/products`, {
      params: { search: search.value || undefined, sort: sort.value, dir: dir.value, page: page.value }
    });
    const { items, meta: m } = getPaginated(res); products.value = items; meta.value = m;
  } finally { loading.value = false; }
}
function nextPage(){ if (meta.value && page.value < meta.value.last_page) page.value++; }
function prevPage(){ if (meta.value && page.value > 1) page.value--; }

watch(() => [props.categorySlug], () => { page.value=1; fetchProducts(); });
watch(() => [search.value, sort.value, dir.value, page.value], fetchProducts);
onMounted(fetchProducts);
</script>

<template>
  <div v-if="!props.categorySlug" class="text-sm text-ink-900/60">Select a category to view products.</div>

  <div v-else>
    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end mb-4">
      <div class="flex-1">
        <label class="label">Search</label>
        <input v-model="search" class="input" placeholder="Find a drink…">
      </div>
      <div>
        <label class="label">Sort by</label>
        <select v-model="sort" class="select">
          <option value="created_at">Newest</option><option value="name">Name</option><option value="price">Price</option>
        </select>
      </div>
      <div>
        <label class="label">Direction</label>
        <select v-model="dir" class="select"><option value="desc">Desc</option><option value="asc">Asc</option></select>
      </div>
    </div>

    <!-- Grid -->
    <div v-if="loading" class="text-sm text-ink-900/60">Loading products…</div>
    <div v-else-if="products.length===0" class="text-sm text-ink-900/60">No products.</div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <div v-for="p in products" :key="p.id" class="card">
      <div class="card-body">
        <div class="flex items-start justify-between gap-3">
          <h3 class="font-semibold">{{ p.name }}</h3>
          <span class="badge">€{{ p.price }}</span>
        </div>
        <p v-if="p.description" class="text-sm text-ink-900/70 mt-1 line-clamp-2">{{ p.description }}</p>
        <button class="btn btn-primary mt-3" @click="addToCart(p)">Add to cart</button>
      </div>
    </div>
  </div>

    <!-- Pager -->
    <div v-if="meta" class="mt-6 flex items-center justify-center gap-3">
      <button class="btn btn-secondary" :disabled="page<=1" @click="prevPage">Previous</button>
      <span class="text-sm text-ink-900/70">Page {{ meta.current_page }} / {{ meta.last_page }}</span>
      <button class="btn btn-secondary" :disabled="page>=meta.last_page" @click="nextPage">Next</button>
    </div>
  </div>
</template>
