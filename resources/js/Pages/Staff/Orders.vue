<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import CafeBranchPicker from '@/Components/CafeBranchPicker.vue'
import axios from 'axios'
import { useCafeStore } from '@/stores/cafe'
import Badge from '@/Components/ui/Badge.vue'
import { paymentStatusPill } from '@/utils/paymentStatus'

const page = usePage()
const scope = computed(() => page.props.staffScope || { canChooseBranch:true, lockedBranchId:null })
const cafe = useCafeStore(); cafe.hydrate()

const pending   = ref([]); const loadingPending   = ref(false)
const preparing = ref([]); const loadingPreparing = ref(false)
const ready     = ref([]); const loadingReady     = ref(false)

const errorMsg = ref(null)
const auto     = ref(true)
const intervalMs = 7000
let timer = null

// kur branch është i bllokuar, i marrim detajet vetëm për shfaqje
const lockedBranch = ref(null)
async function loadLockedBranch() {
  if (!scope.value.lockedBranchId) return
  try {
    // kërkon auth: /api/manage/branches/{id} (e ke në routes/manage)
    const res = await axios.get(`/api/manage/branches/${scope.value.lockedBranchId}`)
    lockedBranch.value = res.data
  } catch { /* ok */ }
}

const paramsBase = () => ({
  cafe_id: cafe.cafe?.id ?? undefined,
  // nëse është i bllokuar → përdorim lockedBranchId; përndryshe store.branch.id (nëse ka)
  branch_id: scope.value.lockedBranchId ?? (cafe.branch?.id ?? undefined),
})

function statusBadgeClass(s) {
  switch (s) {
    case 'pending':   return 'bg-accent-50 text-accent-700 border border-accent-200'
    case 'preparing': return 'bg-brand-50 text-brand-700 border border-brand-200'
    case 'ready':     return 'bg-leaf-50 text-leaf-700 border border-leaf-200'
    case 'delivered': return 'bg-gray-100 text-gray-700 border border-gray-200'
    case 'canceled':  return 'bg-red-50 text-red-700 border border-red-200'
    default:          return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

async function fetchStatus(status) {
  const loadingMap = { pending: loadingPending, preparing: loadingPreparing, ready: loadingReady }
  const dataMap    = { pending, preparing, ready }
  loadingMap[status].value = true
  try {
    const res = await axios.get('/api/orders', { params: { ...paramsBase(), status } })
    const body = res.data
    const items = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
    dataMap[status].value = items
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load orders.'
  } finally {
    loadingMap[status].value = false
  }
}
async function fetchAll() { await Promise.all([fetchStatus('pending'), fetchStatus('preparing'), fetchStatus('ready')]) }

function startAuto() { stopAuto(); if (auto.value) timer = setInterval(fetchAll, intervalMs) }
function stopAuto() { if (timer) { clearInterval(timer); timer = null } }

async function updateStatus(order, to) {
  try { await axios.patch(`/api/orders/${order.id}/status`, { status: to }); await fetchAll() }
  catch (e) { errorMsg.value = e.response?.data?.message || 'Failed to update status.' }
}
function nextLabel(s) { if (s==='pending') return 'Start Preparing'; if (s==='preparing') return 'Mark Ready'; if (s==='ready') return 'Mark Delivered'; return null }
function nextStatus(s){ if (s==='pending') return 'preparing'; if (s==='preparing') return 'ready'; if (s==='ready') return 'delivered'; return null }

// ----- NEW: helper për Paid (preferon o.total_paid; fallback nga o.payments në cents) -----
function totalPaid(o) {
  if (o && o.total_paid != null) return o.total_paid
  const payments = Array.isArray(o?.payments) ? o.payments : []
  let cents = 0
  for (const p of payments) {
    if (p?.status === 'succeeded' && p?.amount != null) {
      const v = Number(p.amount)
      if (!Number.isNaN(v)) cents += Math.round(v * 100)
    }
  }
  return (cents / 100).toFixed(2)
}

onMounted(async () => {
  await loadLockedBranch()
  await fetchAll()
  startAuto()
})
onUnmounted(stopAuto)
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-brand-700">Staff Board</h1>
      <div class="flex items-center gap-3">
        <label class="text-sm text-ink-900/70 flex items-center gap-2">
          <input type="checkbox" v-model="auto" @change="startAuto" class="rounded border-brand-200">
          Auto-refresh
        </label>
        <button class="btn btn-secondary" @click="fetchAll">Refresh</button>
      </div>
    </div>

    <!-- Nëse përdoruesi NUK mund të zgjedhë branch -->
    <div v-if="!scope.canChooseBranch" class="card mb-5">
      <div class="card-body flex items-center justify-between">
        <div>
          <div class="text-sm text-ink-900/70">Working branch</div>
          <div class="font-semibold">
            {{ lockedBranch?.name ?? ('#'+ (scope.lockedBranchId ?? '')) }}
          </div>
        </div>
        <span class="badge">Locked</span>
      </div>
    </div>

    <!-- Përndryshe: lejo zgjedhje cafe/branch -->
    <div v-else class="card mb-5"><div class="card-body">
      <CafeBranchPicker />
    </div></div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">
      {{ errorMsg }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <!-- PENDING -->
      <section>
        <header class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Pending</h2>
          <span class="badge">{{ pending.length }}</span>
        </header>
        <div class="space-y-3">
          <div v-if="loadingPending" class="text-sm text-ink-900/60">Loading…</div>
          <article v-for="o in pending" :key="o.id" class="card">
            <div class="card-body">
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
                  <span class="badge" :class="statusBadgeClass(o.status)">{{ o.status }}</span>
                </div>
              </div>
              <ul class="mt-2 text-sm text-ink-900/80 space-y-1">
                <li v-for="it in (o.items || [])" :key="it.id">×{{ it.quantity }} — {{ it.product?.name ?? it.product_name ?? 'Item' }}</li>
              </ul>
              <div class="mt-3 flex items-center justify-between">
                <div class="text-sm text-ink-900/70">
                  Total: <strong>€{{ o.total_price }}</strong>
                  <span class="mx-2">·</span>
                  Paid: <strong>€{{ totalPaid(o) }}</strong>
                </div>
                <div class="flex gap-2">
                  <button class="btn btn-primary" @click="updateStatus(o, nextStatus('pending'))">{{ nextLabel('pending') }}</button>
                  <button class="btn btn-ghost" @click="updateStatus(o, 'canceled')">Cancel</button>
                </div>
              </div>
            </div>
          </article>
          <div v-if="!loadingPending && pending.length===0" class="text-sm text-ink-900/60">No pending orders.</div>
        </div>
      </section>

      <!-- PREPARING -->
      <section>
        <header class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Preparing</h2>
          <span class="badge">{{ preparing.length }}</span>
        </header>
        <div class="space-y-3">
          <div v-if="loadingPreparing" class="text-sm text-ink-900/60">Loading…</div>
          <article v-for="o in preparing" :key="o.id" class="card">
            <div class="card-body">
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
                  <span class="badge" :class="statusBadgeClass(o.status)">{{ o.status }}</span>
                </div>
              </div>
              <ul class="mt-2 text-sm text-ink-900/80 space-y-1">
                <li v-for="it in (o.items || [])" :key="it.id">×{{ it.quantity }} — {{ it.product?.name ?? it.product_name ?? 'Item' }}</li>
              </ul>
              <div class="mt-3 flex items-center justify-between">
                <div class="text-sm text-ink-900/70">
                  Total: <strong>€{{ o.total_price }}</strong>
                  <span class="mx-2">·</span>
                  Paid: <strong>€{{ totalPaid(o) }}</strong>
                </div>
                <div class="flex gap-2">
                  <button class="btn btn-primary" @click="updateStatus(o, nextStatus('preparing'))">{{ nextLabel('preparing') }}</button>
                  <button class="btn btn-ghost" @click="updateStatus(o, 'canceled')">Cancel</button>
                </div>
              </div>
            </div>
          </article>
          <div v-if="!loadingPreparing && preparing.length===0" class="text-sm text-ink-900/60">No preparing orders.</div>
        </div>
      </section>

      <!-- READY -->
      <section>
        <header class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Ready</h2>
          <span class="badge">{{ ready.length }}</span>
        </header>
        <div class="space-y-3">
          <div v-if="loadingReady" class="text-sm text-ink-900/60">Loading…</div>
          <article v-for="o in ready" :key="o.id" class="card">
            <div class="card-body">
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
                  <span class="badge" :class="statusBadgeClass(o.status)">{{ o.status }}</span>
                </div>
              </div>
              <ul class="mt-2 text-sm text-ink-900/80 space-y-1">
                <li v-for="it in (o.items || [])" :key="it.id">×{{ it.quantity }} — {{ it.product?.name ?? it.product_name ?? 'Item' }}</li>
              </ul>
              <div class="mt-3 flex items-center justify-between">
                <div class="text-sm text-ink-900/70">
                  Total: <strong>€{{ o.total_price }}</strong>
                  <span class="mx-2">·</span>
                  Paid: <strong>€{{ totalPaid(o) }}</strong>
                </div>
                <div class="flex gap-2">
                  <button class="btn btn-primary" @click="updateStatus(o, nextStatus('ready'))">{{ nextLabel('ready') }}</button>
                  <button class="btn btn-ghost" @click="updateStatus(o, 'canceled')">Cancel</button>
                </div>
              </div>
            </div>
          </article>
          <div v-if="!loadingReady && ready.length===0" class="text-sm text-ink-900/60">No ready orders.</div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
