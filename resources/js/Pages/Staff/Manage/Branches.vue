<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import StaffLayout from '@/Layouts/StaffLayout.vue'
import { useCafeStore } from '@/stores/cafe'
import axios from 'axios'

const cafeStore = useCafeStore(); cafeStore.hydrate()
const items = ref([]), loading = ref(false), errorMsg = ref(null)
const search = ref('')
const page = ref(1)
const showModal = ref(false), editingItem = ref(null)
const form = ref({ name: '', slug: '', address: '', phone: '', opening_hours: '', is_active: true })

const paramsBase = computed(() => ({
  cafe_id: cafeStore.cafe?.id ?? undefined,
  search: search.value || undefined,
  page: page.value,
}))

async function fetchItems() {
  loading.value = true
  try {
    const res = await axios.get('/api/manage/branches', { params: paramsBase.value })
    const body = res.data
    items.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load branches'
  } finally {
    loading.value = false
  }
}

async function toggleActive(branch) {
  try {
    await axios.patch(`/api/manage/branches/${branch.id}/toggle`)
    branch.is_active = !branch.is_active
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to update branch'
  }
}

function openModal(item = null) {
  editingItem.value = item
  if (item) {
    form.value = { ...item }
  } else {
    form.value = { name: '', slug: '', address: '', phone: '', opening_hours: '', is_active: true }
  }
  showModal.value = true
}

async function saveItem() {
  try {
    const data = { ...form.value, cafe_id: cafeStore.cafe?.id }
    if (editingItem.value) {
      await axios.put(`/api/manage/branches/${editingItem.value.id}`, data)
    } else {
      await axios.post('/api/manage/branches', data)
    }
    showModal.value = false
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to save branch'
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return
  try {
    await axios.delete(`/api/manage/branches/${item.id}`)
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to delete branch'
  }
}

watch([() => cafeStore.cafe?.id, search, page], fetchItems)
onMounted(fetchItems)
</script>

<template>
  <StaffLayout title="Manage • Branches" :showPicker="true">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <input v-model="search" class="input" placeholder="Search branches..." />
      </div>
      <button class="btn" @click="openModal()">New Branch</button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">{{ errorMsg }}</div>

    <div class="rounded-xl border border-cream/60 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-cream/60 text-ink-900/70">
          <tr>
            <th class="text-left p-3">Name</th>
            <th class="text-left p-3">Phone</th>
            <th class="text-left p-3">Address</th>
            <th class="text-left p-3">Hours</th>
            <th class="text-left p-3">Active</th>
            <th class="text-right p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="p-3 text-ink-900/60">Loading…</td></tr>
          <tr v-for="b in items" :key="b.id" class="border-t">
            <td class="p-3">{{ b.name }}</td>
            <td class="p-3">{{ b.phone || '-' }}</td>
            <td class="p-3 text-ink-900/60">{{ b.address }}</td>
            <td class="p-3">{{ b.opening_hours || '-' }}</td>
            <td class="p-3">
              <button @click="toggleActive(b)" class="badge cursor-pointer" :class="b.is_active ? 'bg-leaf-50 text-leaf-700 border border-leaf-200' : 'bg-ink-900/5 text-ink-900/60 border'">
                {{ b.is_active ? 'Yes' : 'No' }}
              </button>
            </td>
            <td class="p-3 text-right">
              <button class="btn btn-ghost" @click="openModal(b)">Edit</button>
              <button class="btn btn-ghost text-red-600" @click="deleteItem(b)">Delete</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length===0"><td colspan="6" class="p-3 text-ink-900/60">No branches found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingItem ? 'Edit Branch' : 'New Branch' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" class="input w-full" placeholder="Branch name" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Slug</label>
            <input v-model="form.slug" class="input w-full" placeholder="branch-slug" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea v-model="form.address" class="input w-full" rows="3" placeholder="Full address"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input v-model="form.phone" class="input w-full" placeholder="Phone number" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Opening Hours</label>
            <input v-model="form.opening_hours" class="input w-full" placeholder="e.g., Mon-Fri 8:00-18:00" />
          </div>
          
          <div class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" class="rounded border-brand-200" />
            <label class="text-sm">Active</label>
          </div>
        </div>
        
        <div class="flex gap-2 mt-6">
          <button class="btn flex-1" @click="saveItem()">Save</button>
          <button class="btn btn-secondary flex-1" @click="showModal = false">Cancel</button>
        </div>
      </div>
    </div>
  </StaffLayout>
</template>
