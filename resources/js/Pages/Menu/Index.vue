<!-- resources/js/Pages/Menu/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CafeBranchPicker from '@/Components/CafeBranchPicker.vue';
import { useCafeStore } from '@/stores/cafe';

const store = useCafeStore();
store.hydrate();
</script>

<template>
  <AppLayout>
    <h1 class="text-xl font-semibold mb-4">Menu</h1>
    <CafeBranchPicker />

    <div class="mt-6 text-sm text-gray-600">
      <div v-if="store.cafe">Café: <strong>{{ store.cafe.name }}</strong></div>
      <div v-if="store.branch">Branch: <strong>{{ store.branch.name }}</strong></div>
      <div v-else-if="store.cafe">Branch: <em>(none selected)</em></div>
    </div>

    <!-- Next: load categories/products based on store.cafe.slug -->
  </AppLayout>
</template>
