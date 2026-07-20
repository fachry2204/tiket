<template>
  <div class="p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-white">FAQ (Tanya Jawab)</h1>
      <button @click="openModal()" class="btn-primary flex items-center gap-2">
        <span>+</span> Tambah FAQ
      </button>
    </div>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat data FAQ...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="faqs.length === 0" class="p-8 text-center text-white/40">
        Belum ada data FAQ yang dibuat.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-6 py-4 font-medium w-16 text-center">Urutan</th>
              <th class="px-6 py-4 font-medium">Pertanyaan</th>
              <th class="px-6 py-4 font-medium text-center">Status</th>
              <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="f in faqs" :key="f.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 text-center text-white/40 font-mono">{{ f.order_num }}</td>
              <td class="px-6 py-4">
                <div class="font-bold text-white mb-1">{{ f.question }}</div>
                <div class="text-white/60 text-xs line-clamp-2">{{ f.answer }}</div>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="f.is_active ? 'badge-paid' : 'badge-rejected'" class="badge">
                  {{ f.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-3">
                <button @click="openModal(f)" class="text-primary hover:text-white transition-colors">Edit</button>
                <button @click="deleteFaq(f.id)" class="text-red-400 hover:text-red-300 transition-colors">Hapus</button>
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
          <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit' : 'Tambah' }} FAQ</h2>
          <button @click="closeModal" class="text-white/50 hover:text-white">✕</button>
        </div>
        
        <form @submit.prevent="saveFaq" class="p-6 overflow-y-auto space-y-4">
          <div>
            <label class="label-field">Pertanyaan *</label>
            <input v-model="form.question" type="text" class="input-field" placeholder="Apakah tiket bisa di-refund?" required />
          </div>
          
          <div>
            <label class="label-field">Jawaban *</label>
            <textarea v-model="form.answer" class="input-field min-h-[100px]" required></textarea>
          </div>
          
          <div>
            <label class="label-field">Urutan Tampil *</label>
            <input v-model="form.order_num" type="number" class="input-field" required min="1" />
            <div class="text-xs text-white/40 mt-1">Angka lebih kecil tampil lebih atas (1, 2, 3...)</div>
          </div>
          
          <div class="flex items-center gap-3 pt-2">
            <input v-model="form.is_active" type="checkbox" class="rounded border-white/20 bg-dark-surface text-primary" />
            <label class="text-sm text-white/60">Aktif (Ditampilkan di halaman FAQ)</label>
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

const faqs = ref<any[]>([])
const loading = ref(true)
const showModal = ref(false)
const processing = ref(false)
const error = ref('')

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  question: '',
  answer: '',
  order_num: 1,
  is_active: true
})

async function fetchFaqs() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/faqs')
    faqs.value = data.data
  } catch (e) {
    console.error('Failed to fetch faqs', e)
  } finally {
    loading.value = false
  }
}

function openModal(f: any = null) {
  error.value = ''
  if (f) {
    isEditing.value = true
    editingId.value = f.id
    form.value = {
      question: f.question,
      answer: f.answer,
      order_num: f.order_num,
      is_active: f.is_active
    }
  } else {
    isEditing.value = false
    editingId.value = null
    form.value = {
      question: '',
      answer: '',
      order_num: faqs.value.length + 1,
      is_active: true
    }
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function saveFaq() {
  processing.value = true
  error.value = ''
  
  try {
    if (isEditing.value && editingId.value) {
      await api.put(`/admin/faqs/${editingId.value}`, form.value)
    } else {
      await api.post('/admin/faqs', form.value)
    }
    closeModal()
    fetchFaqs()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    processing.value = false
  }
}

async function deleteFaq(id: number) {
  if (!confirm('Anda yakin ingin menghapus FAQ ini?')) return
  
  try {
    await api.delete(`/admin/faqs/${id}`)
    fetchFaqs()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus FAQ')
  }
}

onMounted(() => {
  fetchFaqs()
})
</script>
