<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Topbar from '@/Components/Staff/Topbar.vue'
defineProps({ title: { type: String, default: 'Admin' } })
</script>

<template>
  <AppLayout>
    <Topbar :title="title" mode="admin" :canChooseBranch="false" />
    <div class="mx-auto max-w-7xl px-4 py-6">
      <!-- Admin nav i thjeshtë -->
      <nav class="mb-4 flex flex-wrap gap-2 text-sm">
        <a href="/admin/overview" class="btn btn-ghost">Overview</a>
        <a href="/admin/cafes" class="btn btn-ghost">Cafes</a>
        <a href="/admin/branches" class="btn btn-ghost">Branches</a>
        <a href="/admin/users" class="btn btn-ghost">Users</a>
        <a href="/admin/orders" class="btn btn-ghost">All Orders</a>
      </nav>
    <div class="rounded-lg border border-cream/60 bg-white p-4"><slot /></div>
    </div>
  </AppLayout>
</template>

<style scoped>
.border-cream\/60 { border-color: rgba(250,247,242,.6); }
</style>
