<template>
  <div class="p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-white">Rekening Bank</h1>
      <button @click="openModal()" class="btn-primary flex items-center gap-2">
        <span>+</span> Tambah Rekening
      </button>
    </div>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat data rekening...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="accounts.length === 0" class="p-8 text-center text-white/40">
        Belum ada data rekening bank.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-6 py-4 font-medium">Bank</th>
              <th class="px-6 py-4 font-medium">No. Rekening</th>
              <th class="px-6 py-4 font-medium">Atas Nama</th>
              <th class="px-6 py-4 font-medium text-center">Status</th>
              <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="acc in accounts" :key="acc.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 font-bold text-white">{{ acc.bank_name }}</td>
              <td class="px-6 py-4 font-mono text-electric">{{ acc.account_number }}</td>
              <td class="px-6 py-4 text-white">{{ acc.account_holder_name }}</td>
              <td class="px-6 py-4 text-center">
                <span :class="acc.is_active ? 'badge-paid' : 'badge-rejected'" class="badge">
                  {{ acc.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-3">
                <button @click="openModal(acc)" class="text-primary hover:text-white transition-colors">Edit</button>
                <button @click="deleteAccount(acc.id)" class="text-red-400 hover:text-red-300 transition-colors">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeModal">
      <div class="card-glass w-full max-w-md rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-white/10 flex justify-between items-center">
          <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit' : 'Tambah' }} Rekening</h2>
          <button @click="closeModal" class="text-white/50 hover:text-white">✕</button>
        </div>
        
        <form @submit.prevent="saveAccount" class="p-6 overflow-y-auto space-y-4">
          <div>
            <label class="label-field">Nama Bank *</label>
            <input v-model="form.bank_name" type="text" class="input-field" placeholder="BCA / Mandiri / BNI" required />
          </div>
          <div>
            <label class="label-field">Nomor Rekening *</label>
            <input v-model="form.account_number" type="text" class="input-field font-mono" placeholder="1234567890" required />
          </div>
          <div>
            <label class="label-field">Atas Nama *</label>
            <input v-model="form.account_holder_name" type="text" class="input-field" placeholder="PT Masivers Community" required />
          </div>
          <div class="flex items-center gap-3 pt-2">
            <input v-model="form.is_active" type="checkbox" class="rounded border-white/20 bg-dark-surface text-primary" />
            <label class="text-sm text-white/60">Aktif (Ditampilkan ke pembeli)</label>
          </div>
          
          <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">
            {{ error }}
          </div>
          
          <div class="pt-4 flex gap-3">
            <button type="button" @click="closeModal" class="flex-1 btn-outline">Batal</button>
            <button type="submit" :disabled="processing" class="flex-1 btn-primary">
              {{ processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const accounts = ref<any[]>([])
const loading = ref(true)
const showModal = ref(false)
const processing = ref(false)
const error = ref('')

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  bank_name: '',
  account_number: '',
  account_holder_name: '',
  is_active: true
})

async function fetchAccounts() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/bank-accounts')
    accounts.value = data.data
  } catch (e) {
    console.error('Failed to fetch accounts', e)
  } finally {
    loading.value = false
  }
}

function openModal(acc: any = null) {
  error.value = ''
  if (acc) {
    isEditing.value = true
    editingId.value = acc.id
    form.value = {
      bank_name: acc.bank_name,
      account_number: acc.account_number,
      account_holder_name: acc.account_holder_name,
      is_active: acc.is_active
    }
  } else {
    isEditing.value = false
    editingId.value = null
    form.value = {
      bank_name: '',
      account_number: '',
      account_holder_name: '',
      is_active: true
    }
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function saveAccount() {
  processing.value = true
  error.value = ''
  try {
    if (isEditing.value && editingId.value) {
      await api.put(`/admin/bank-accounts/${editingId.value}`, form.value)
    } else {
      await api.post('/admin/bank-accounts', form.value)
    }
    closeModal()
    fetchAccounts()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    processing.value = false
  }
}

async function deleteAccount(id: number) {
  if (!confirm('Anda yakin ingin menghapus rekening ini?')) return
  
  try {
    await api.delete(`/admin/bank-accounts/${id}`)
    fetchAccounts()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus')
  }
}

onMounted(() => {
  fetchAccounts()
})
</script>
