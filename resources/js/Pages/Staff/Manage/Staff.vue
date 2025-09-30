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
const canManageStaff = computed(() => isAdmin.value || isOwner.value || isManager.value)

const cafeStore = useCafeStore(); cafeStore.hydrate()
const items = ref([]), loading = ref(false), errorMsg = ref(null)
const search = ref(''), roleFilter = ref(''), cafeFilter = ref('')
const pageNum = ref(1)
const availableCafes = ref([])
const showModal = ref(false), editingItem = ref(null)
const showUserModal = ref(false)
const form = ref({ 
  user_id: '', 
  position: '', 
  branch_id: '', 
  is_active: true 
})
const userForm = ref({ name: '', email: '', password: '' })
const branches = ref([])
const availableUsers = ref([])
const existingUsers = ref([])
const deleting = ref({}) // Track which items are being deleted

const paramsBase = computed(() => ({
  cafe_id: (cafeFilter.value || cafeStore.cafe?.id) ?? undefined,
  search: search.value || undefined,
  role: roleFilter.value || undefined,
  page: pageNum.value,
}))

async function fetchItems() {
  loading.value = true
  try {
    const res = await axios.get('/api/manage/staff', { params: paramsBase.value })
    const body = res.data
    items.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to load staff'
  } finally {
    loading.value = false
  }
}


async function fetchBranches() {
  try {
    // Use the selected cafe ID (admin filter or current cafe)
    const cafeId = cafeFilter.value || cafeStore.cafe?.id
    console.log('Fetching branches for cafe ID:', cafeId)
    
    if (!cafeId) {
      console.log('No cafe ID available, clearing branches')
      branches.value = []
      return
    }
    
    const res = await axios.get('/api/manage/branches', { params: { cafe_id: cafeId } })
    console.log('Branches API response:', res.data)
    
    const body = res.data
    const branchData = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
    branches.value = branchData
    console.log('Processed branches:', branchData)
  } catch (e) {
    console.error('Failed to load branches:', e)
    branches.value = []
  }
}

async function fetchAvailableUsers() {
  try {
    const res = await axios.get('/api/users/available-for-staff', { 
      params: { cafe_id: cafeStore.cafe?.id } 
    })
    availableUsers.value = res.data || []
  } catch (e) {
    console.error('Failed to load users:', e)
  }
}

async function fetchExistingUsers() {
  try {
    const res = await axios.get('/api/users/available-for-staff', { 
      params: { cafe_id: cafeStore.cafe?.id } 
    })
    existingUsers.value = res.data || []
  } catch (e) {
    console.error('Failed to load existing users:', e)
  }
}

async function fetchAvailableCafes() {
  try {
    const res = await axios.get('/api/manage/cafes')
    const body = res.data
    availableCafes.value = Array.isArray(body) ? body : (Array.isArray(body?.data) ? body.data : [])
  } catch (e) {
    console.error('Failed to load cafes:', e)
  }
}

async function openModal(item = null) {
  console.log('Opening modal, cafe context:', {
    cafeStore: cafeStore.cafe,
    cafeFilter: cafeFilter.value,
    cafeId: cafeFilter.value || cafeStore.cafe?.id
  })
  
  editingItem.value = item
  if (item) {
    form.value = { 
      user_id: item.user_id || '', 
      position: item.position || '', 
      branch_id: item.branch_id || '', 
      is_active: item.is_active ?? true 
    }
  } else {
    form.value = { 
      user_id: '', 
      position: '', 
      branch_id: '', 
      is_active: true 
    }
  }
  
  // Refetch branches when opening modal to ensure we have the latest data
  await fetchBranches()
  showModal.value = true
}

function openUserModal() {
  userForm.value = { name: '', email: '', password: '' }
  showUserModal.value = true
}

async function createUser() {
  try {
    const res = await axios.post('/api/users', userForm.value)
    showUserModal.value = false
    await fetchExistingUsers()
    // Auto-select the newly created user
    form.value.user_id = res.data.id
    showModal.value = true
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to create user'
  }
}

async function saveItem() {
  try {
    const data = { 
      ...form.value, 
      cafe_id: cafeFilter.value || cafeStore.cafe?.id
    }
    
    console.log('Saving staff profile with data:', data)
    
    if (editingItem.value) {
      await axios.put(`/api/manage/staff/profiles/${editingItem.value.id}`, data)
    } else {
      await axios.post('/api/manage/staff/profiles', data)
    }
    showModal.value = false
    await fetchItems()
  } catch (e) {
    const errorMessage = e.response?.data?.message || 'Failed to save staff profile'
    
    // Handle duplicate staff profile error
    if (errorMessage.includes('already exists') || errorMessage.includes('unique')) {
      errorMsg.value = 'This user already has a staff profile for this cafe and branch combination.'
    } else {
      errorMsg.value = errorMessage
    }
  }
}

async function deleteItem(item) {
  if (!confirm(`Remove "${item.user?.name}" from ${item.cafe?.name || 'this cafe'}? This will remove their ${item.position} access.`)) return
  
  deleting.value[item.id] = true
  try {
    await axios.delete(`/api/manage/staff/profiles/${item.id}`)
    
    // Show success message briefly
    const originalError = errorMsg.value
    errorMsg.value = `Successfully removed ${item.user?.name} from staff.`
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      errorMsg.value = originalError
    }, 3000)
    
    await fetchItems()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Failed to remove staff member'
  } finally {
    deleting.value[item.id] = false
  }
}

watch([() => cafeStore.cafe?.id, search, roleFilter, cafeFilter, pageNum], fetchItems)
watch([cafeFilter, () => cafeStore.cafe?.id], fetchBranches)
onMounted(() => {
  fetchItems()
  fetchBranches()
  fetchExistingUsers()
  fetchAvailableCafes()
})
</script>

<template>
  <StaffLayout title="Manage • Staff" :showPicker="true">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <input v-model="search" class="input" placeholder="Search staff..." />
        <select v-model="roleFilter" class="select">
          <option value="">All roles</option>
          <option value="manager">Manager</option>
          <option value="waiter">Waiter</option>
          <option value="bartender">Bartender</option>
        </select>
        <select v-if="isAdmin" v-model="cafeFilter" class="select">
          <option value="">All cafes</option>
          <option v-for="c in availableCafes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <button v-if="canManageStaff" class="btn" @click="openModal()">Add Staff</button>
    </div>

    <div v-if="errorMsg" class="rounded-lg bg-red-50 text-red-700 border border-red-200 px-4 py-2 text-sm mb-4">{{ errorMsg }}</div>

    <div class="rounded-xl border border-cream/60 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-cream/60 text-ink-900/70">
          <tr>
            <th class="text-left p-3">User</th>
            <th class="text-left p-3">Position</th>
            <th v-if="isAdmin" class="text-left p-3">Cafe</th>
            <th class="text-left p-3">Branch</th>
            <th class="text-left p-3">Status</th>
            <th class="text-right p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td :colspan="isAdmin ? 6 : 5" class="p-3 text-ink-900/60">Loading…</td></tr>
          <tr v-for="s in items" :key="s.id" class="border-t">
            <td class="p-3">
              <div>{{ s.user?.name ?? 'Unknown' }}</div>
              <div class="text-xs text-ink-900/60">{{ s.user?.email ?? '' }}</div>
            </td>
            <td class="p-3">
              <span class="badge" :class="{
                'bg-brand-50 text-brand-700 border border-brand-200': s.position === 'owner',
                'bg-accent-50 text-accent-700 border border-accent-200': s.position === 'manager',
                'bg-leaf-50 text-leaf-700 border border-leaf-200': s.position === 'waiter',
                'bg-ink-900/5 text-ink-900/60 border': s.position === 'bartender'
              }">
                {{ s.position }}
              </span>
            </td>
            <td v-if="isAdmin" class="p-3">{{ s.cafe?.name ?? 'Unknown' }}</td>
            <td class="p-3">{{ s.branch?.name ?? 'All branches' }}</td>
            <td class="p-3">
              <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 rounded-full" :class="s.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                {{ s.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-3 text-right">
              <button v-if="canManageStaff" class="btn btn-ghost" @click="openModal(s)">Edit</button>
              <button 
                v-if="canManageStaff" 
                class="btn btn-ghost text-red-600" 
                @click="deleteItem(s)"
                :disabled="deleting[s.id]"
              >
                {{ deleting[s.id] ? 'Removing...' : 'Remove' }}
              </button>
              <span v-else class="text-ink-900/40 text-xs">View only</span>
            </td>
          </tr>
          <tr v-if="!loading && items.length===0"><td :colspan="isAdmin ? 6 : 5" class="p-3 text-ink-900/60">No staff found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Add Staff Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingItem ? 'Edit Staff' : 'Add Staff Member' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">User</label>
            <select v-model="form.user_id" class="select w-full">
              <option value="">Select existing user</option>
              <option v-for="u in existingUsers" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
            <button type="button" class="btn btn-ghost text-sm mt-1" @click="openUserModal()">+ Create new user</button>
            <p class="text-xs text-ink-900/60 mt-1">
              Note: Users can have multiple staff profiles for different cafes or branches.
            </p>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Position</label>
            <select v-model="form.position" class="select w-full">
              <option value="">Select position</option>
              <option v-if="isAdmin || isOwner" value="manager">Manager</option>
              <option value="waiter">Waiter</option>
              <option value="bartender">Bartender</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Branch (optional)</label>
            <select v-model="form.branch_id" class="select w-full">
              <option value="">All branches</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <p class="text-xs text-ink-900/60 mt-1">
              {{ branches.length }} branches available for cafe ID: {{ cafeFilter || cafeStore.cafe?.id || 'none' }}
            </p>
          </div>
        </div>
        
        <div class="flex gap-2 mt-6">
          <button class="btn flex-1" @click="saveItem()">{{ editingItem ? 'Update' : 'Add' }}</button>
          <button class="btn btn-secondary flex-1" @click="showModal = false">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Quick User Creation Modal -->
    <div v-if="showUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showUserModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold mb-4">Quick Create User</h3>
        
        <div class="space-y-3">
          <input v-model="userForm.name" class="input w-full" placeholder="Full name" />
          <input v-model="userForm.email" type="email" class="input w-full" placeholder="email@example.com" />
          <input v-model="userForm.password" type="password" class="input w-full" placeholder="Password (optional)" />
        </div>
        
        <div class="flex gap-2 mt-4">
          <button class="btn flex-1" @click="createUser()">Create</button>
          <button class="btn btn-secondary flex-1" @click="showUserModal = false">Cancel</button>
        </div>
      </div>
    </div>
  </StaffLayout>
</template>
