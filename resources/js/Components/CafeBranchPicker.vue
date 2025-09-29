<!-- resources/js/Components/CafeBranchPicker.vue -->
<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import axios from 'axios';
import { useCafeStore } from '@/stores/cafe';

const store = useCafeStore();
const cafes    = ref([]);
const branches = ref([]);
const loadingCafes = ref(false);
const loadingBranches = ref(false);

const unwrap = (res) => Array.isArray(res.data) ? res.data : (res.data?.data ?? []);

async function loadCafes() {
  loadingCafes.value = true;
  try {
    const res = await axios.get('/api/cafes');
    cafes.value = unwrap(res);
  } finally {
    loadingCafes.value = false;
  }
}

async function loadBranches(slug) {
  if (!slug) { branches.value = []; return; }
  loadingBranches.value = true;
  try {
    const res = await axios.get(`/api/cafes/${slug}/branches`);
    branches.value = unwrap(res);
  } finally {
    loadingBranches.value = false;
  }
}

function onCafeChange(e) {
  const id = Number(e.target.value || 0);
  const cafe = cafes.value.find(c => c.id === id) || null;
  store.setCafe(cafe);
  loadBranches(cafe?.slug);
}
function onBranchChange(e) {
  const id = Number(e.target.value || 0);
  const branch = branches.value.find(b => b.id === id) || null;
  store.setBranch(branch);
}

onMounted(async () => {
  store.hydrate();
  await loadCafes();
  // nëse kemi zgjedhje ekzistuese, ngarko degët
  if (store.cafe?.slug) await loadBranches(store.cafe.slug);
});

console.log(branches)
</script>

<template>
  <div class="grid gap-3 sm:grid-cols-2">
    <!-- Café -->
    <div>
      <label class="block text-sm text-gray-600 mb-1">Café</label>
      <select
        class="w-full border rounded-md p-2 text-sm"
        :disabled="loadingCafes"
        @change="onCafeChange"
      >
        <option :value="''">Zgjidh një café…</option>
        <option v-for="c in cafes" :key="c.id" :value="c.id" :selected="store.cafe?.id === c.id">
          {{ c.name }}
        </option>
      </select>
    </div>

    <!-- Branch -->
    <div>
      <label class="block text-sm text-gray-600 mb-1">Branch</label>
      <select
        class="w-full border rounded-md p-2 text-sm"
        :disabled="!store.cafe || loadingBranches"
        @change="onBranchChange"
      >
        <option :value="''">(opsionale) Zgjidh degën…</option>
        <option v-for="b in branches" :key="b.id" :value="b.id" :selected="store.branch?.id === b.id">
          {{ b.name }} — {{ b.address }}
        </option>
      </select>
    </div>
  </div>
</template>
