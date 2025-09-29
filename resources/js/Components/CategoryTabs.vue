<!-- resources/js/Components/CategoryTabs.vue -->
<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useCafeStore } from '@/stores/cafe';
const emit = defineEmits(['select']);
const store = useCafeStore();
const categories = ref([]); const active = ref(null); const loading = ref(false);
const unwrap = (res) => Array.isArray(res.data) ? res.data : (res.data?.data ?? []);
async function load() {
  if (!store.cafe?.slug) { categories.value=[]; active.value=null; emit('select', null); return; }
  loading.value = true;
  try {
    categories.value = unwrap(await axios.get(`/api/cafes/${store.cafe.slug}/categories`));
    active.value = categories.value[0]?.slug ?? null; emit('select', active.value);
  } finally { loading.value = false; }
}
function pick(slug){ active.value = slug; emit('select', slug); }
watch(() => store.cafe?.slug, load); onMounted(load);
</script>

<template>
  <div class="overflow-x-auto no-scrollbar">
    <div class="inline-flex gap-2">
      <button
        v-for="c in categories" :key="c.id" @click="pick(c.slug)"
        class="px-3 py-1.5 rounded-full text-sm border transition"
        :class="active===c.slug
          ? 'bg-brand-600 text-white border-brand-600'
          : 'bg-white border-brand-200 hover:bg-brand-50'"
      >{{ c.name }}</button>
      <span v-if="loading" class="text-sm text-ink-900/60 px-2">Loading…</span>
    </div>
  </div>
</template>

<style>
.no-scrollbar::-webkit-scrollbar{ display:none; }
.no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
</style>
