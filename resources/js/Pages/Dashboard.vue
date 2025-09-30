<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import Badge from '@/Components/ui/Badge.vue'
import { paymentStatusPill } from '@/utils/paymentStatus'

const orders = ref([])
const loading = ref(false)
const errorMsg = ref(null)

const recentOrders = computed(() => orders.value.slice(0, 5))

const stats = computed(() => {
  const total = orders.value.length
  const pending = orders.value.filter(o => o.status === 'pending').length
  const ready = orders.value.filter(o => o.status === 'ready').length
  const totalSpent = orders.value
    .filter(o => o.payment_status === 'paid')
    .reduce((sum, o) => sum + parseFloat(o.total_price), 0)
  
  return { total, pending, ready, totalSpent: totalSpent.toFixed(2) }
})

async function fetchRecentOrders() {
  loading.value = true
  try {
    const res = await axios.get('/api/my-orders', { params: { limit: 10 } })
    const body = res.data
    orders.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load orders'
  } finally {
    loading.value = false
  }
}

function statusBadgeClass(status) {
  switch (status) {
    case 'pending': return 'bg-yellow-50 text-yellow-700 border border-yellow-200'
    case 'preparing': return 'bg-blue-50 text-blue-700 border border-blue-200'
    case 'ready': return 'bg-green-50 text-green-700 border border-green-200'
    case 'delivered': return 'bg-gray-50 text-gray-700 border border-gray-200'
    case 'cancelled': return 'bg-red-50 text-red-700 border border-red-200'
    default: return 'bg-gray-50 text-gray-700 border border-gray-200'
  }
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(fetchRecentOrders)
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-brand-700">Dashboard</h1>
      <button class="btn btn-secondary" @click="fetchRecentOrders" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-6">
      {{ errorMsg }}
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="card">
        <div class="card-body text-center">
          <div class="text-2xl font-semibold text-brand-700">{{ stats.total }}</div>
          <div class="text-sm text-ink-900/60">Total Orders</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body text-center">
          <div class="text-2xl font-semibold text-yellow-600">{{ stats.pending }}</div>
          <div class="text-sm text-ink-900/60">Pending</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body text-center">
          <div class="text-2xl font-semibold text-green-600">{{ stats.ready }}</div>
          <div class="text-sm text-ink-900/60">Ready</div>
        </div>
      </div>
      <div class="card">
        <div class="card-body text-center">
          <div class="text-2xl font-semibold text-brand-700">€{{ stats.totalSpent }}</div>
          <div class="text-sm text-ink-900/60">Total Spent</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid sm:grid-cols-2 gap-5 mb-6">
      <div class="card">
        <div class="card-body">
          <h2 class="font-semibold mb-2">Order Food</h2>
          <p class="text-sm text-ink-900/70 mb-3">Browse menus and place new orders from your favorite cafes.</p>
          <a href="/menu" class="btn btn-primary">Browse Menu</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h2 class="font-semibold mb-2">Order History</h2>
          <p class="text-sm text-ink-900/70 mb-3">View all your past orders and payment status.</p>
          <a href="/my-orders" class="btn btn-secondary">View Orders</a>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold">Recent Orders</h2>
          <a href="/my-orders" class="text-sm text-brand-700 hover:underline">View all →</a>
        </div>

        <div v-if="loading" class="text-center py-4 text-ink-900/60">Loading...</div>
        
        <div v-else-if="recentOrders.length === 0" class="text-center py-8 text-ink-900/60">
          <p>No orders yet.</p>
          <a href="/menu" class="btn btn-primary mt-2">Start Ordering</a>
        </div>

        <div v-else class="space-y-3">
          <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex-1">
              <div class="font-medium">Order #{{ order.id }}</div>
              <div class="text-sm text-ink-900/60">
                {{ order.cafe?.name }} • {{ formatDate(order.created_at) }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <Badge
                v-if="order.payment_status"
                :label="paymentStatusPill(order.payment_status).label"
                :tone="paymentStatusPill(order.payment_status).tone"
              />
              <span class="badge" :class="statusBadgeClass(order.status)">
                {{ order.status }}
              </span>
              <span class="font-medium">€{{ order.total_price }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
