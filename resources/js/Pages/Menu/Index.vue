<!-- resources/js/Pages/Menu/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CafeBranchPicker from '@/Components/CafeBranchPicker.vue';
import CategoryTabs from '@/Components/CategoryTabs.vue';
import ProductsGrid from '@/Components/ProductsGrid.vue';
import { useCafeStore } from '@/stores/cafe';
import { ref } from 'vue';

const store = useCafeStore(); store.hydrate();
const selectedCategory = ref(null);
function onSelect(slug) { selectedCategory.value = slug; }
</script>

<template>
  <AppLayout>
    <h1 class="text-xl font-semibold mb-4 text-brand-700">Menu</h1>

    <div class="card mb-5"><div class="card-body">
      <CafeBranchPicker />
    </div></div>

    <div class="mb-4">
      <CategoryTabs @select="onSelect" />
    </div>

    <ProductsGrid :categorySlug="selectedCategory" />
  </AppLayout>
</template>
