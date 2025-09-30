<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const has = (r) => roles.value.includes(r)
const isAdmin = computed(() => has('admin'))
</script>

<template>
  <StaffLayout title="Manage" :showPicker="!isAdmin">
    <div class="flex items-center justify-between mb-5">
      <h1 class="text-xl font-semibold text-brand-700">Management</h1>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <a href="/staff/manage/cafes" class="card"><div class="card-body">Cafes</div></a>
      <a href="/staff/manage/branches" class="card"><div class="card-body">Branches</div></a>
      <a href="/staff/manage/categories" class="card"><div class="card-body">Categories</div></a>
      <a href="/staff/manage/products" class="card"><div class="card-body">Products</div></a>
      <a href="/staff/manage/staff" class="card"><div class="card-body">Staff</div></a>
    </div>
  </StaffLayout>
</template>


