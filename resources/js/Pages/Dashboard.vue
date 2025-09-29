<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
  <AppLayout>
    <div class="grid sm:grid-cols-2 gap-5">
      <div class="card"><div class="card-body">
        <h2 class="font-semibold mb-1">Getting started</h2>
        <p class="text-sm text-ink-900/70">Pick a café and start taking orders.</p>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="font-semibold mb-1">Today</h2>
        <p class="text-sm text-ink-900/70">No orders yet.</p>
      </div></div>
    </div>
  </AppLayout>
</template>
