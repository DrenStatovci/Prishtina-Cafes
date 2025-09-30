<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Topbar from '@/Components/Staff/Topbar.vue'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const is = (r) => roles.value.includes(r)
const canManage = computed(() => is('owner') || is('manager') || is('admin'))
const scope = computed(() => page.props.staffScope || { canChooseBranch:true, lockedBranchId:null })

defineProps({
  title: { type: String, default: 'Staff' },
  showPicker: { type: Boolean, default: true },
})
</script>

<template>
  <AppLayout>
    <Topbar :title="title" mode="staff" :canChooseBranch="showPicker && scope.canChooseBranch" />

    <!-- Përmbajtja -->
    <div class="mx-auto max-w-7xl px-4 py-6">
      <slot />
    </div>
  </AppLayout>
</template>

<style scoped>
:root { --cream: #FAF7F2; }
.bg-cream\/80 { background-color: rgba(250,247,242, .8); }
.border-cream\/70 { border-color: rgba(250,247,242, .7); }
</style>
