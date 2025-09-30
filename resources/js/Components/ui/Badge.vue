<script setup>
defineProps({
  label: { type: String, required: true },
  tone: {
    type: String,
    default: 'ink', // 'brand'|'accent'|'leaf'|'ink'|'danger'|'warning'
  },
  title: { type: String, default: '' },
})
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
    :class="{
      'bg-[#7C4A2D]/10 text-[#7C4A2D]': tone === 'brand',
      'bg-[#E2911A]/10 text-[#E2911A]': tone === 'accent',
      'bg-[#2F8E66]/10 text-[#2F8E66]': tone === 'leaf',
      'bg-gray-200 text-gray-800': !tone || tone === 'ink',
      'bg-red-100 text-red-700': tone === 'danger',
      'bg-amber-100 text-amber-700': tone === 'warning',
    }"
    :title="title"
  >
    {{ label }}
  </span>
</template>
