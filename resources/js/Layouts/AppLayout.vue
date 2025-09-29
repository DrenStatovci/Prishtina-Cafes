<!-- resources/js/Layouts/AppLayout.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, Head } from '@inertiajs/vue3'

const page = usePage()
const user  = computed(() => page.props.auth?.user)
const roles = computed(() => page.props.auth?.roles ?? [])
const is = (r) => roles.value.includes(r)
const canManage = computed(() => is('owner') || is('manager') || is('admin'))
const isStaff   = computed(() => is('waiter') || is('bartender') || canManage.value)
const mobileOpen = ref(false)

const dark = ref(false)
onMounted(() => {
  dark.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', dark.value)
})
function toggleDark() {
  dark.value = !dark.value
  localStorage.setItem('theme', dark.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', dark.value)
}
</script>

<template>
  <Head />
  <div class="min-h-screen">
    <!-- Topbar -->
    <header class="sticky top-0 z-30 backdrop-blur bg-white/90 border-b border-brand-100">
      <div class="container">
        <div class="h-16 flex items-center gap-4">
          <Link href="/" class="font-semibold tracking-tight text-lg flex items-center gap-2">
            <span class="text-brand-600">☕</span><span>Prishtina Cafes</span>
          </Link>

          <nav class="hidden md:flex items-center gap-6 text-sm">
            <Link href="/dashboard" class="hover:text-brand-700">Dashboard</Link>
            <Link href="/menu" class="hover:text-brand-700">Menu</Link>
            <Link v-if="isStaff" href="/staff/orders" class="hover:text-brand-700">Orders</Link>
            <Link v-if="canManage" href="/manage/products" class="hover:text-brand-700">Manage</Link>
          </nav>

          <div class="ml-auto flex items-center gap-2">
            <button class="btn btn-ghost" @click="toggleDark" title="Toggle theme">
              <span v-if="!dark">🌙</span><span v-else>☀️</span>
            </button>
            <div class="hidden md:flex items-center gap-3">
              <span v-if="user" class="text-sm text-ink-900/70">Hi, {{ user.name }}</span>
              <Link v-if="user" href="/logout" method="post" as="button" class="btn btn-secondary">Logout</Link>
              <Link v-else href="/login" class="btn btn-primary">Login</Link>
            </div>
            <button class="md:hidden btn btn-ghost" @click="mobileOpen = !mobileOpen" aria-label="Menu">☰</button>
          </div>
        </div>

        <!-- Mobile -->
        <div v-show="mobileOpen" class="md:hidden pb-4">
          <nav class="flex flex-col gap-2 text-sm">
            <Link href="/dashboard" class="btn btn-ghost justify-start">Dashboard</Link>
            <Link href="/menu" class="btn btn-ghost justify-start">Menu</Link>
            <Link v-if="isStaff" href="/staff/orders" class="btn btn-ghost justify-start">Orders</Link>
            <Link v-if="canManage" href="/manage/products" class="btn btn-ghost justify-start">Manage</Link>
            <div class="border-t my-2 hr"></div>
            <div class="flex items-center justify-between">
              <span v-if="user" class="text-sm text-ink-900/70">Hi, {{ user.name }}</span>
              <Link v-if="user" href="/logout" method="post" as="button" class="btn btn-secondary">Logout</Link>
              <Link v-else href="/login" class="btn btn-primary">Login</Link>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <!-- Page -->
    <main class="container py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t hr">
      <div class="container py-8 text-xs text-ink-900/60 flex items-center justify-between">
        <span>© {{ new Date().getFullYear() }} Prishtina Cafes</span>
        <span>Laravel + Inertia + Tailwind</span>
      </div>
    </footer>
  </div>
</template>
