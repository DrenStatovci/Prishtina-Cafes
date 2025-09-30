<script setup>
import CafeBranchPicker from '@/Components/CafeBranchPicker.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const isAdmin = computed(() => roles.value.includes('admin'))

defineProps({
  title: { type: String, default: 'Staff' },
  mode:  { type: String, default: 'staff' }, // 'staff' | 'admin'
  canChooseBranch: { type: Boolean, default: true },
})
</script>

<template>
  <div class="sticky top-0 z-20 bg-cream/80 backdrop-blur border-b border-cream/70">
    <div class="mx-auto max-w-7xl px-4 pt-3 pb-2 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <h1 class="text-lg md:text-xl font-semibold text-brand-700">{{ title }}</h1>
        <span class="hidden md:inline-block text-ink-900/60 text-sm">•</span>
        <span class="inline-flex items-center gap-2 text-sm text-ink-900/70">
          <span class="inline-block h-2 w-2 rounded-full" :class="mode==='admin' ? 'bg-[#1F2937]' : 'bg-leaf-500'"></span>
          {{ mode==='admin' ? 'Admin zone' : 'Staff zone' }}
        </span>
      </div>

      <div v-if="mode==='staff'" class="w-full md:w-auto">
        <div class="flex flex-col md:flex-row md:items-center md:gap-6 gap-3">
          <!-- <nav class="flex items-center gap-2 text-sm">
            <a href="/staff/dashboard" class="tab">Dashboard</a>
            <a href="/staff/orders" class="tab">Orders</a>
            <a href="/manage/products" class="tab">Manage</a>
          </nav> -->
          <div v-if="canChooseBranch" class="md:min-w-[360px]">
            <CafeBranchPicker />
          </div>
          <!-- <div v-else class="text-sm text-ink-900/70">
            <span class="px-2 py-1 rounded-full bg-brand-50 text-brand-700 border border-brand-200">Branch locked</span>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:root { --cream: #FAF7F2; }
.bg-cream\/80 { background-color: rgba(250,247,242,.8); }
.border-cream\/70 { border-color: rgba(250,247,242,.7); }
.tab { padding: 0.375rem 0.75rem; border-radius: 0.5rem; color: #1F2937; }
.tab:hover { background-color: rgba(124,74,45,.06); color: #7C4A2D; }
</style>
