<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import Badge from '@/Components/ui/Badge.vue'
import { paymentStatusPill } from '@/utils/paymentStatus'

const orders = ref([])
const loading = ref(false)
const errorMsg = ref(null)
const paying = ref({}) // Track which orders are being paid

const statusFilter = ref('')

const filteredOrders = computed(() => {
  if (!statusFilter.value) return orders.value
  return orders.value.filter(order => order.status === statusFilter.value)
})

async function fetchOrders() {
  loading.value = true
  try {
    const res = await axios.get('/api/my-orders', {
      params: { status: statusFilter.value || undefined }
    })
    const body = res.data
    orders.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load orders'
  } finally {
    loading.value = false
  }
}

async function payOrder(order) {
  if (order.payment_status === 'paid') return
  
  paying.value[order.id] = true
  try {
    const res = await axios.post(`/api/orders/${order.id}/pay-mock`)
    // Update the order with new payment status
    const updatedOrder = orders.value.find(o => o.id === order.id)
    if (updatedOrder) {
      updatedOrder.payment_status = res.data.order.payment_status
      updatedOrder.total_paid = res.data.order.total_paid
    }
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Payment failed'
  } finally {
    paying.value[order.id] = false
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
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(fetchOrders)
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-brand-700">My Orders</h1>
      <button class="btn btn-secondary" @click="fetchOrders" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">
      {{ errorMsg }}
    </div>

    <!-- Filter -->
    <div class="mb-4">
      <select v-model="statusFilter" @change="fetchOrders" class="select">
        <option value="">All orders</option>
        <option value="pending">Pending</option>
        <option value="preparing">Preparing</option>
        <option value="ready">Ready</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <!-- Orders List -->
    <div v-if="loading" class="text-center py-8 text-ink-900/60">Loading orders...</div>
    
    <div v-else-if="filteredOrders.length === 0" class="text-center py-8 text-ink-900/60">
      No orders found.
    </div>

    <div v-else class="space-y-4">
      <div v-for="order in filteredOrders" :key="order.id" class="card">
        <div class="card-body">
          <!-- Order Header -->
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-semibold text-lg">Order #{{ order.id }}</h3>
              <p class="text-sm text-ink-900/60">{{ formatDate(order.created_at) }}</p>
              <p class="text-sm text-ink-900/60">
                {{ order.cafe?.name }} 
                <span v-if="order.branch"> - {{ order.branch.name }}</span>
              </p>
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
            </div>
          </div>

          <!-- Order Items -->
          <div class="mb-4">
            <h4 class="font-medium mb-2">Items:</h4>
            <ul class="space-y-1">
              <li v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                <span>×{{ item.quantity }} {{ item.product?.name || 'Item' }}</span>
                <span>€{{ item.line_total }}</span>
              </li>
            </ul>
          </div>

          <!-- Order Details -->
          <div class="flex items-center justify-between text-sm text-ink-900/70 mb-4">
            <div>
              <span v-if="order.table_number">Table {{ order.table_number }}</span>
              <span v-if="order.payment_preference"> • {{ order.payment_preference }}</span>
            </div>
            <div class="text-right">
              <div class="font-semibold text-lg">Total: €{{ order.total_price }}</div>
              <div v-if="order.total_paid > 0" class="text-green-600">
                Paid: €{{ order.total_paid }}
              </div>
            </div>
          </div>

          <!-- Payment Button -->
          <div v-if="order.payment_status !== 'paid'" class="flex justify-end">
            <button 
              class="btn btn-primary"
              @click="payOrder(order)"
              :disabled="paying[order.id]"
            >
              {{ paying[order.id] ? 'Processing...' : 'Pay Now' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
