<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import StaffLayout from '@/Layouts/StaffLayout.vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const isAdmin = computed(() => roles.value.includes('admin'))

const items = ref([]), loading = ref(false), errorMsg = ref(null)
const search = ref('')
const pageNum = ref(1)
const showModal = ref(false), editingItem = ref(null)
const form = ref({ name: '', phone: '', email: '', owner_id: '', is_active: true })
const availableUsers = ref([])

const paramsBase = computed(() => ({
  search: search.value || undefined,
  page: pageNum.value,
}))

async function fetchItems() {
  loading.value = true
  try {
    const res = await axios.get('/api/manage/cafes', { params: paramsBase.value })
    const body = res.data
    items.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load cafes'
  } finally {
    loading.value = false
  }
}

async function fetchAvailableUsers() {
  try {
    const res = await axios.get('/api/users/available-owners')
    availableUsers.value = res.data || []
  } catch (e) {
    console.error('Failed to load users:', e)
  }
}

async function toggleActive(cafe) {
  try {
    await axios.patch(`/api/manage/cafes/${cafe.id}/toggle`)
    cafe.is_active = !cafe.is_active
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to update cafe'
  }
}

function openModal(item = null) {
  editingItem.value = item
  if (item) {
    form.value = { ...item, owner_id: item.owner_id || '' }
  } else {
    form.value = { name: '', phone: '', email: '', owner_id: '', is_active: true }
  }
  showModal.value = true
}

async function saveItem() {
  try {
    const data = { ...form.value }
    if (editingItem.value) {
      await axios.put(`/api/manage/cafes/${editingItem.value.id}`, data)
    } else {
      await axios.post('/api/manage/cafes', data)
    }
    showModal.value = false
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to save cafe'
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return
  try {
    await axios.delete(`/api/manage/cafes/${item.id}`)
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to delete cafe'
  }
}

watch([search, pageNum], fetchItems)
onMounted(() => {
  fetchItems()
  fetchAvailableUsers()
})
</script>

<template>
  <StaffLayout title="Manage • Cafes" :showPicker="false">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <input v-model="search" class="input" placeholder="Search cafes..." />
      </div>
      <button v-if="isAdmin" class="btn" @click="openModal()">New Cafe</button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">{{ errorMsg }}</div>

    <div class="rounded-xl border border-cream/60 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-cream/60 text-ink-900/70">
          <tr>
            <th class="text-left p-3">Name</th>
            <th class="text-left p-3">Owner</th>
            <th class="text-left p-3">Phone</th>
            <th class="text-left p-3">Email</th>
            <th class="text-left p-3">Active</th>
            <th class="text-right p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="p-3 text-ink-900/60">Loading…</td></tr>
          <tr v-for="c in items" :key="c.id" class="border-t">
            <td class="p-3">{{ c.name }}</td>
            <td class="p-3">
              <div>{{ c.owner?.name ?? 'Unknown' }}</div>
              <div class="text-xs text-ink-900/60">{{ c.owner?.email ?? '' }}</div>
            </td>
            <td class="p-3">{{ c.phone }}</td>
            <td class="p-3">{{ c.email }}</td>
            <td class="p-3">
              <button v-if="isAdmin" @click="toggleActive(c)" class="badge cursor-pointer" :class="c.is_active ? 'bg-leaf-50 text-leaf-700 border border-leaf-200' : 'bg-ink-900/5 text-ink-900/60 border'">
                {{ c.is_active ? 'Yes' : 'No' }}
              </button>
              <span v-else class="badge" :class="c.is_active ? 'bg-leaf-50 text-leaf-700 border border-leaf-200' : 'bg-ink-900/5 text-ink-900/60 border'">
                {{ c.is_active ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="p-3 text-right">
              <button v-if="isAdmin" class="btn btn-ghost" @click="openModal(c)">Edit</button>
              <button v-if="isAdmin" class="btn btn-ghost text-red-600" @click="deleteItem(c)">Delete</button>
              <span v-else class="text-ink-900/40 text-xs">View only</span>
            </td>
          </tr>
          <tr v-if="!loading && items.length===0"><td colspan="6" class="p-3 text-ink-900/60">No cafes found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingItem ? 'Edit Cafe' : 'New Cafe' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" class="input w-full" placeholder="Cafe name" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input v-model="form.phone" class="input w-full" placeholder="Phone number" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model="form.email" type="email" class="input w-full" placeholder="email@example.com" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Owner</label>
            <select v-model="form.owner_id" class="select w-full">
              <option value="">Select owner</option>
              <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
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
