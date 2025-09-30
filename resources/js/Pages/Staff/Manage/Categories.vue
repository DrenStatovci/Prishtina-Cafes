<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import StaffLayout from '@/Layouts/StaffLayout.vue'
import { useCafeStore } from '@/stores/cafe'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const isAdmin = computed(() => roles.value.includes('admin'))
const isOwner = computed(() => roles.value.includes('owner'))
const isManager = computed(() => roles.value.includes('manager'))
const canManageCategories = computed(() => isAdmin.value || isOwner.value || isManager.value)

const cafeStore = useCafeStore(); cafeStore.hydrate()
const items = ref([]), loading = ref(false), errorMsg = ref(null)
const search = ref('')
const pageNum = ref(1)
const showModal = ref(false), editingItem = ref(null)
const form = ref({ name: '', is_active: true })

const paramsBase = computed(() => ({
  cafe_id: cafeStore.cafe?.id ?? undefined,
  search: search.value || undefined,
  page: pageNum.value,
}))

async function fetchItems() {
  loading.value = true
  try {
    const res = await axios.get('/api/manage/categories', { params: paramsBase.value })
    const body = res.data
    items.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load categories'
  } finally {
    loading.value = false
  }
}

async function toggleActive(category) {
  try {
    await axios.patch(`/api/manage/categories/${category.id}/toggle`)
    category.is_active = !category.is_active
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to update category'
  }
}

function openModal(item = null) {
  editingItem.value = item
  if (item) {
    form.value = { ...item }
  } else {
    form.value = { name: '', is_active: true }
  }
  showModal.value = true
}

async function saveItem() {
  try {
    const data = { ...form.value, cafe_id: cafeStore.cafe?.id }
    if (editingItem.value) {
      await axios.put(`/api/manage/categories/${editingItem.value.id}`, data)
    } else {
      await axios.post('/api/manage/categories', data)
    }
    showModal.value = false
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to save category'
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return
  try {
    await axios.delete(`/api/manage/categories/${item.id}`)
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to delete category'
  }
}

watch([() => cafeStore.cafe?.id, search, pageNum], fetchItems)
onMounted(fetchItems)
</script>

<template>
  <StaffLayout title="Manage • Categories" :showPicker="true">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <input v-model="search" class="input" placeholder="Search categories..." />
      </div>
      <button v-if="canManageCategories" class="btn" @click="openModal()">New Category</button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">{{ errorMsg }}</div>

    <div class="rounded-xl border border-cream/60 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-cream/60 text-ink-900/70">
          <tr>
            <th class="text-left p-3">Name</th>
            <th class="text-left p-3">Slug</th>
            <th class="text-left p-3">Active</th>
            <th class="text-right p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="p-3 text-ink-900/60">Loading…</td></tr>
          <tr v-for="c in items" :key="c.id" class="border-t">
            <td class="p-3">{{ c.name }}</td>
            <td class="p-3 text-ink-900/60">{{ c.slug }}</td>
            <td class="p-3">
              <button @click="toggleActive(c)" class="badge cursor-pointer" :class="c.is_active ? 'bg-leaf-50 text-leaf-700 border border-leaf-200' : 'bg-ink-900/5 text-ink-900/60 border'">
                {{ c.is_active ? 'Yes' : 'No' }}
              </button>
            </td>
            <td class="p-3 text-right">
              <button v-if="canManageCategories" class="btn btn-ghost" @click="openModal(c)">Edit</button>
              <button v-if="canManageCategories" class="btn btn-ghost text-red-600" @click="deleteItem(c)">Delete</button>
              <span v-else class="text-ink-900/40 text-xs">View only</span>
            </td>
          </tr>
          <tr v-if="!loading && items.length===0"><td colspan="4" class="p-3 text-ink-900/60">No categories found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingItem ? 'Edit Category' : 'New Category' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" class="input w-full" placeholder="Category name" />
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
