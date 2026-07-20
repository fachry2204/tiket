<template>
  <div class="p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-white">Kategori Tiket</h1>
      <button @click="openModal()" class="btn-primary flex items-center gap-2">
        <span>+</span> Tambah Tiket
      </button>
    </div>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat data tiket...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="products.length === 0" class="p-8 text-center text-white/40">
        Belum ada kategori tiket yang dibuat.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-6 py-4 font-medium">Nama Tiket</th>
              <th class="px-6 py-4 font-medium text-right">Harga</th>
              <th class="px-6 py-4 font-medium text-center">Kuota Sisa</th>
              <th class="px-6 py-4 font-medium text-center">Status</th>
              <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="p in products" :key="p.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 font-bold text-white">{{ p.name }}</td>
              <td class="px-6 py-4 text-right">
                <div v-if="p.promo_price" class="text-white/40 line-through text-xs">{{ formatRupiah(p.price) }}</div>
                <div class="font-bold text-accent">{{ formatRupiah(p.promo_price || p.price) }}</div>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="text-white">{{ p.available_quota }}</span>
                <span class="text-white/40 text-xs ml-1">/ {{ p.total_quota }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="p.is_active ? 'badge-paid' : 'badge-rejected'" class="badge">
                  {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-3">
                <button @click="openModal(p)" class="text-primary hover:text-white transition-colors">Edit</button>
                <button @click="deleteProduct(p.id)" class="text-red-400 hover:text-red-300 transition-colors">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeModal">
      <div class="card-glass w-full max-w-lg rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-white/10 flex justify-between items-center">
          <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit' : 'Tambah' }} Tiket</h2>
          <button @click="closeModal" class="text-white/50 hover:text-white">✕</button>
        </div>
        
        <form @submit.prevent="saveProduct" class="p-6 overflow-y-auto space-y-4">
          <div>
            <label class="label-field">Nama Tiket *</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="Presale 1 / VIP / dsb" required />
          </div>
          
          <div>
            <label class="label-field">Deskripsi Singkat</label>
            <textarea v-model="form.description" class="input-field" rows="2" placeholder="Fasilitas atau info tambahan..."></textarea>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label-field">Harga Normal (Rp) *</label>
              <input v-model="form.price" type="number" class="input-field" required min="0" />
            </div>
            <div>
              <label class="label-field">Harga Promo (Rp)</label>
              <input v-model="form.promo_price" type="number" class="input-field" placeholder="Kosongkan jika tidak ada" min="0" />
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label-field">Total Kuota *</label>
              <input v-model="form.total_quota" type="number" class="input-field" required min="1" />
            </div>
            <div>
              <label class="label-field">Maks Pembelian *</label>
              <input v-model="form.max_per_order" type="number" class="input-field" required min="1" />
            </div>
          </div>
          
          <div class="flex items-center gap-6 pt-2">
            <div class="flex items-center gap-3">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-white/20 bg-dark-surface text-primary" />
              <label for="is_active" class="text-sm text-white/60 cursor-pointer">Aktif (Bisa dibeli)</label>
            </div>
            
            <div class="flex items-center gap-3">
              <input v-model="form.is_special" type="checkbox" id="is_special" class="rounded border-red-500/50 bg-dark-surface text-red-500 focus:ring-red-500" />
              <label for="is_special" class="text-sm text-red-400 font-bold cursor-pointer">Special Tiket</label>
            </div>
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

const products = ref<any[]>([])
const loading = ref(true)
const showModal = ref(false)
const processing = ref(false)
const error = ref('')

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  name: '',
  description: '',
  price: '',
  promo_price: '',
  total_quota: '',
  max_per_order: '5',
  is_active: true,
  is_special: false
})

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/ticket-products')
    products.value = data.data
  } catch (e) {
    console.error('Failed to fetch products', e)
  } finally {
    loading.value = false
  }
}

function openModal(p: any = null) {
  error.value = ''
  if (p) {
    isEditing.value = true
    editingId.value = p.id
    form.value = {
      name: p.name,
      description: p.description || '',
      price: p.price,
      promo_price: p.promo_price || '',
      total_quota: p.total_quota,
      max_per_order: p.max_per_order,
      is_active: p.is_active,
      is_special: p.is_special
    }
  } else {
    isEditing.value = false
    editingId.value = null
    form.value = {
      name: '',
      description: '',
      price: '',
      promo_price: '',
      total_quota: '',
      max_per_order: '5',
      is_active: true,
      is_special: false
    }
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function saveProduct() {
  processing.value = true
  error.value = ''
  
  const payload = {
    ...form.value,
    quota: form.value.total_quota ? Number(form.value.total_quota) : 0,
    promo_price: form.value.promo_price ? Number(form.value.promo_price) : null,
    event_id: 1, // Default event
    status: 'available'
  }
  
  try {
    if (isEditing.value && editingId.value) {
      await api.put(`/admin/ticket-products/${editingId.value}`, payload)
    } else {
      await api.post('/admin/ticket-products', payload)
    }
    closeModal()
    fetchProducts()
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const firstError = Object.values(e.response.data.errors)[0] as string[]
      error.value = firstError[0]
    } else {
      error.value = e.response?.data?.message || 'Terjadi kesalahan'
    }
  } finally {
    processing.value = false
  }
}

async function deleteProduct(id: number) {
  if (!confirm('Anda yakin ingin menghapus kategori tiket ini?')) return
  
  try {
    await api.delete(`/admin/ticket-products/${id}`)
    fetchProducts()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus tiket')
  }
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

onMounted(() => {
  fetchProducts()
})
</script>
