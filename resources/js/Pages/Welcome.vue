<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Link, Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Welcome" />
  <div class="min-h-screen flex flex-col items-center justify-center bg-cream-50 text-ink-900">
    <div class="max-w-lg text-center px-6">
      <h1 class="text-3xl font-semibold mb-2 text-brand-700">☕ Cafe MVP</h1>
      <p class="text-ink-900/70 mb-6">Order fast. Manage simply.</p>
      <div class="flex items-center justify-center gap-3">
        <Link href="/login" class="btn btn-secondary">Log in</Link>
        <Link href="/register" class="btn btn-primary">Register</Link>
      </div>
      <div class="mt-8 text-sm text-ink-900/60">
        Or browse the public menu when it’s ready.
      </div>
    </div>
  </div>
</template>
