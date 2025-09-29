<!-- resources/js/Components/CafeBranchPicker.vue -->
<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useCafeStore } from '@/stores/cafe';

const store = useCafeStore();
const cafes = ref([]), branches = ref([]);
const loadingCafes = ref(false), loadingBranches = ref(false);

const unwrap = (res) => Array.isArray(res.data) ? res.data : (res.data?.data ?? []);

async function loadCafes() {
  loadingCafes.value = true;
  try { cafes.value = unwrap(await axios.get('/api/cafes')); }
  finally { loadingCafes.value = false; }
}
async function loadBranches(slug) {
  if (!slug) { branches.value = []; return; }
  loadingBranches.value = true;
  try { branches.value = unwrap(await axios.get(`/api/cafes/${slug}/branches`)); }
  finally { loadingBranches.value = false; }
}
function onCafeChange(e) {
  const id = Number(e.target.value || 0);
  const cafe = cafes.value.find(c => c.id === id) || null;
  store.setCafe(cafe); loadBranches(cafe?.slug);
}
function onBranchChange(e) {
  const id = Number(e.target.value || 0);
  const branch = branches.value.find(b => b.id === id) || null;
  store.setBranch(branch);
}

onMounted(async () => {
  store.hydrate(); await loadCafes();
  if (store.cafe?.slug) await loadBranches(store.cafe.slug);
});
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="label">Café</label>
      <select class="select" :disabled="loadingCafes" @change="onCafeChange">
        <option :value="''">Choose a café…</option>
        <option v-for="c in cafes" :key="c.id" :value="c.id" :selected="store.cafe?.id===c.id">{{ c.name }}</option>
      </select>
    </div>
    <div>
      <label class="label">Branch</label>
      <select class="select" :disabled="!store.cafe || loadingBranches" @change="onBranchChange">
        <option :value="''">(optional) Choose a branch…</option>
        <option v-for="b in branches" :key="b.id" :value="b.id" :selected="store.branch?.id===b.id">
          {{ b.name }} — {{ b.address }}
        </option>
      </select>
    </div>
  </div>
</template>
