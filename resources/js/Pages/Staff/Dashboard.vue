<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import StaffLayout from '@/Layouts/StaffLayout.vue'
import { useCafeStore } from '@/stores/cafe'
import Badge from '@/Components/ui/Badge.vue'
import { paymentStatusPill } from '@/utils/paymentStatus'

const cafe = useCafeStore(); cafe.hydrate()
const page = usePage()
const scope = computed(() => page.props.staffScope || { canChooseBranch:true, lockedBranchId:null })

const loading = ref(false)
const pending = ref([]); const preparing = ref([]); const ready = ref([])
const errorMsg = ref(null)

const paramsBase = () => ({
  cafe_id: cafe.cafe?.id ?? undefined,
  branch_id: scope.value.lockedBranchId ?? (cafe.branch?.id ?? undefined),
})

async function fetchAll() {
  loading.value = true
  try {
    const [p1, p2, p3] = await Promise.all([
      axios.get('/api/orders', { params: { ...paramsBase(), status: 'pending'   } }),
      axios.get('/api/orders', { params: { ...paramsBase(), status: 'preparing' } }),
      axios.get('/api/orders', { params: { ...paramsBase(), status: 'ready'     } }),
    ])
    const take = (r) => Array.isArray(r.data) ? r.data : (Array.isArray(r.data?.data) ? r.data.data : [])
    pending.value   = take(p1)
    preparing.value = take(p2)
    ready.value     = take(p3)
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load'
  } finally {
    loading.value = false
  }
}

function sumPaid(orders) {
  let cents = 0
  for (const o of orders) {
    if (o?.payments && Array.isArray(o.payments)) {
      for (const p of o.payments) {
        if (p?.status === 'succeeded' && p?.amount != null) {
          const v = Number(p.amount)
          if (!Number.isNaN(v)) cents += Math.round(v * 100)
        }
      }
    } else if (o?.total_paid != null) {
      const v = Number(o.total_paid)
      if (!Number.isNaN(v)) cents += Math.round(v * 100)
    }
  }
  return (cents / 100).toFixed(2)
}

const kpi = computed(() => ({
  pending:   pending.value.length,
  preparing: preparing.value.length,
  ready:     ready.value.length,
  revenue:   sumPaid([...pending.value, ...preparing.value, ...ready.value]), // revenue “live” në board
}))

// Watch for cafe/branch changes to refetch data
watch(() => [cafe.cafe?.id, cafe.branch?.id], () => {
  fetchAll()
}, { immediate: false })

onMounted(fetchAll)
</script>

<template>
  <StaffLayout title="Staff Dashboard" :showPicker="true">
    <div class="flex items-center justify-end mb-4">
      <button class="btn btn-secondary" :disabled="loading" @click="fetchAll">Refresh</button>
    </div>
    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">
      {{ errorMsg }}
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="rounded-2xl border border-cream/60 bg-white p-4">
        <div class="text-sm text-ink-900/60">Pending</div>
        <div class="text-2xl font-semibold text-ink-900">{{ kpi.pending }}</div>
      </div>
      <div class="rounded-2xl border border-cream/60 bg-white p-4">
        <div class="text-sm text-ink-900/60">Preparing</div>
        <div class="text-2xl font-semibold text-ink-900">{{ kpi.preparing }}</div>
      </div>
      <div class="rounded-2xl border border-cream/60 bg-white p-4">
        <div class="text-sm text-ink-900/60">Ready</div>
        <div class="text-2xl font-semibold text-ink-900">{{ kpi.ready }}</div>
      </div>
      <div class="rounded-2xl border border-cream/60 bg-white p-4">
        <div class="text-sm text-ink-900/60">Revenue (now)</div>
        <div class="text-2xl font-semibold text-ink-900">€{{ kpi.revenue }}</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Orders -->
      <section class="lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold text-ink-900">Recent Orders</h2>
          <a href="/staff/orders" class="text-sm text-brand-700 hover:underline">Open board →</a>
        </div>

        <div class="space-y-3">
          <div v-if="loading" class="text-sm text-ink-900/60">Loading…</div>

          <!-- Render nga listat ekzistuese; marrim 6 më të fundit nga secila -->
          <template v-for="(bucket, name) in { pending, preparing, ready }" :key="name">
            <article v-for="o in bucket.slice(0, 6)" :key="`${name}-${o.id}`" class="rounded-xl border border-cream/60 bg-white p-3">
              <div class="flex items-start justify-between">
                <div class="font-semibold">#{{ o.id }}</div>
                <div class="flex items-center gap-2">
                  <Badge v-if="o.table_number" :label="`Table ${o.table_number}`" tone="brand" />
                  <Badge
                    v-if="o.payment_status"
                    :label="paymentStatusPill(o.payment_status).label"
                    :tone="paymentStatusPill(o.payment_status).tone"
                    :title="`Payment: ${o.payment_status}`"
                  />
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="{
                          'bg-accent-50 text-accent-700 border border-accent-200': name==='pending',
                          'bg-brand-50 text-brand-700 border border-brand-200': name==='preparing',
                          'bg-leaf-50 text-leaf-700 border border-leaf-200': name==='ready',
                        }">
                    {{ name }}
                  </span>
                </div>
              </div>
              <ul class="mt-2 text-sm text-ink-900/80 space-y-1">
                <li v-for="it in (o.items || [])" :key="it.id">
                  ×{{ it.quantity }} — {{ it.product?.name ?? it.product_name ?? 'Item' }}
                </li>
              </ul>
              <div class="mt-2 text-sm text-ink-900/70">
                Total: <strong>€{{ o.total_price }}</strong>
                <span class="mx-2">·</span>
                Paid: <strong>€{{ o.total_paid ?? '0.00' }}</strong>
              </div>
            </article>
          </template>

          <div v-if="!loading && !pending.length && !preparing.length && !ready.length"
               class="text-sm text-ink-900/60">
            No recent orders.
          </div>
        </div>
      </section>

      <!-- Quick Actions -->
      <aside>
        <div class="rounded-2xl border border-cream/60 bg-white p-4">
          <h3 class="font-semibold mb-3 text-ink-900">Quick actions</h3>
          <div class="space-y-2">
            <a href="/staff/orders" class="btn w-full">Open Staff Board</a>
            <a href="/menu" class="btn btn-secondary w-full">Open Menu</a>
          </div>
          <div class="mt-4 text-xs text-ink-900/60">
            Scope: {{ scope.canChooseBranch ? 'Flexible' : 'Locked' }}
          </div>
        </div>
      </aside>
    </div>
  </StaffLayout>
</template>

<style scoped>
:root { --cream: #FAF7F2; }
.border-cream\/60 { border-color: rgba(250,247,242, .6); }
.bg-cream\/80 { background-color: rgba(250,247,242, .8); }
</style>
