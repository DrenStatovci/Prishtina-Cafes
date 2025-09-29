<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Link, Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Welcome" />
  <div class="min-h-screen flex flex-col items-center justify-center bg-white text-gray-800">
    <div class="max-w-lg text-center px-6">
      <h1 class="text-3xl font-semibold mb-3">☕ Prishtina Cafes</h1>
      <p class="text-gray-600 mb-6">Order fast. Manage simply.</p>

      <div class="flex items-center justify-center gap-3">
        <Link href="/login" class="px-4 py-2 rounded-md border hover:bg-gray-50">Log in</Link>
        <Link href="/register" class="px-4 py-2 rounded-md bg-black text-white hover:opacity-90">Register</Link>
      </div>

      <div class="mt-8 text-sm text-gray-500">
        Or browse the public menu when it’s ready.
      </div>
    </div>
  </div>
</template>
