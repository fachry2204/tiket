<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Kelola Admin</h1>
      <button @click="openModal()" class="btn-primary flex items-center gap-2">
        <span>➕</span> Tambah Admin
      </button>
    </div>

    <!-- Data Table -->
    <div class="card-dark rounded-2xl overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-white/40">Memuat data...</div>
      
      <table v-else class="w-full text-sm text-left">
        <thead class="bg-white/5 text-white/60">
          <tr>
            <th class="px-6 py-4 font-medium">Nama / Username</th>
            <th class="px-6 py-4 font-medium">Role</th>
            <th class="px-6 py-4 font-medium">Status</th>
            <th class="px-6 py-4 font-medium">Login Terakhir</th>
            <th class="px-6 py-4 text-right font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="user in users" :key="user.id" class="hover:bg-white/5 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-white">{{ user.name }}</div>
              <div class="text-white/40">{{ user.username }}</div>
            </td>
            <td class="px-6 py-4">
              <span class="badge bg-primary/20 text-primary capitalize">{{ user.role.replace('_', ' ') }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="['badge', user.is_active ? 'badge-paid' : 'badge-expired']">
                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-6 py-4 text-white/40">
              {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString('id-ID') : 'Belum pernah' }}
            </td>
            <td class="px-6 py-4 text-right">
              <button @click="openModal(user)" class="text-blue-400 hover:text-blue-300 mr-3">Edit</button>
              <button @click="deleteUser(user)" class="text-red-400 hover:text-red-300" :disabled="user.id === auth.user?.id">Hapus</button>
            </td>
          </tr>
          <tr v-if="!users.length">
            <td colspan="5" class="px-6 py-8 text-center text-white/40">Belum ada data admin</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="card-dark w-full max-w-md rounded-2xl p-6 relative">
        <button @click="showModal = false" class="absolute top-4 right-4 text-white/40 hover:text-white">✕</button>
        
        <h2 class="text-xl font-bold text-white mb-6">{{ isEdit ? 'Edit Admin' : 'Tambah Admin Baru' }}</h2>
        
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="label-field">Nama Lengkap</label>
            <input v-model="form.name" type="text" class="input-field" required />
          </div>
          <div>
            <label class="label-field">Username</label>
            <input v-model="form.username" type="text" class="input-field" required />
          </div>
          <div>
            <label class="label-field">Role</label>
            <select v-model="form.role" class="input-field bg-dark-card" required>
              <option value="super_admin">Super Admin</option>
              <option value="finance_admin">Finance Admin</option>
              <option value="ticketing_admin">Ticketing Admin</option>
              <option value="viewer">Viewer</option>
            </select>
          </div>
          <div>
            <label class="label-field">Password {{ isEdit ? '(Kosongkan jika tidak diubah)' : '' }}</label>
            <input v-model="form.password" type="password" class="input-field" :required="!isEdit" minlength="8" />
          </div>
          <div class="flex items-center gap-2 pt-2">
            <input v-model="form.is_active" type="checkbox" id="isActive" class="rounded border-white/20 bg-dark-surface text-primary focus:ring-primary" />
            <label for="isActive" class="text-sm text-white/70">Akun Aktif</label>
          </div>

          <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-3 rounded-xl mt-4">
            {{ error }}
          </div>

          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/5">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-white/60 hover:text-white">Batal</button>
            <button type="submit" :disabled="saving" class="btn-primary py-2 px-6">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const users = ref<any[]>([])
const loading = ref(true)

const showModal = ref(false)
const isEdit = ref(false)
const saving = ref(false)
const error = ref('')

const form = ref<{
  id: number | null
  name: string
  username: string
  role: string
  password?: string
  is_active: boolean
}>({
  id: null,
  name: '',
  username: '',
  role: 'finance_admin',
  password: '',
  is_active: true
})

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users')
    users.value = data.data
  } finally {
    loading.value = false
  }
}

function openModal(user?: any) {
  error.value = ''
  if (user) {
    isEdit.value = true
    form.value = { ...user, password: '' }
  } else {
    isEdit.value = false
    form.value = { id: null, name: '', username: '', role: 'finance_admin', password: '', is_active: true }
  }
  showModal.value = true
}

async function saveUser() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    if (isEdit.value && !payload.password) {
      delete payload.password
    }
    
    if (isEdit.value) {
      await api.patch(`/admin/users/${form.value.id}`, payload)
    } else {
      await api.post('/admin/users', payload)
    }
    
    showModal.value = false
    await fetchUsers()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Terjadi kesalahan saat menyimpan'
  } finally {
    saving.value = false
  }
}

async function deleteUser(user: any) {
  if (user.id === auth.user?.id) return
  if (!confirm(`Apakah Anda yakin ingin menghapus admin ${user.name}?`)) return
  
  try {
    await api.delete(`/admin/users/${user.id}`)
    await fetchUsers()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus admin')
  }
}

onMounted(fetchUsers)
</script>
