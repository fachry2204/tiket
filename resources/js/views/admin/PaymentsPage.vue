<template>
  <div class="p-6 md:p-8">
    <h1 class="text-2xl font-bold text-white mb-6">Konfirmasi Pembayaran</h1>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat data pembayaran...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="payments.length === 0" class="p-8 text-center text-white/40">
        Belum ada konfirmasi pembayaran.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-6 py-4 font-medium">Order</th>
              <th class="px-6 py-4 font-medium">Pengirim</th>
              <th class="px-6 py-4 font-medium">Bank</th>
              <th class="px-6 py-4 font-medium">Tanggal Transfer</th>
              <th class="px-6 py-4 font-medium text-right">Nominal</th>
              <th class="px-6 py-4 font-medium text-center">Status</th>
              <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="pay in payments" :key="pay.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4">
                <div class="font-mono text-electric font-bold">{{ pay.order?.order_code }}</div>
                <div class="text-white/60 text-xs">{{ pay.order?.customer?.name }}</div>
              </td>
              <td class="px-6 py-4 text-white">{{ pay.sender_name }}</td>
              <td class="px-6 py-4 text-white">{{ pay.sender_bank }}</td>
              <td class="px-6 py-4 text-white/80">{{ formatDate(pay.transfer_date) }}</td>
              <td class="px-6 py-4 text-right font-bold text-accent">{{ formatRupiah(pay.transferred_amount) }}</td>
              <td class="px-6 py-4 text-center">
                <span :class="statusBadge(pay.status)" class="badge">{{ statusLabel(pay.status) }}</span>
              </td>
              <td class="px-6 py-4 text-right space-x-3">
                <button @click="openModal(pay)" class="text-primary hover:text-white transition-colors">Lihat Bukti</button>
                <button @click="openDeleteModal(pay)" class="text-red-400 hover:text-red-300 transition-colors">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Modal Bukti Bayar -->
    <div v-if="selectedPayment" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeModal">
      <div class="card-glass w-full max-w-2xl rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-white/10 flex justify-between items-center">
          <h2 class="text-xl font-bold text-white">Detail Pembayaran</h2>
          <button @click="closeModal" class="text-white/50 hover:text-white">✕</button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1">
          <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
            <div>
              <div class="text-white/40 mb-1">Kode Order</div>
              <div class="font-mono text-electric font-bold">{{ selectedPayment.order?.order_code || selectedPayment.deleted_order_code || 'Pesanan Terhapus' }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Total Tagihan Order</div>
              <div class="font-bold text-accent">{{ formatRupiah(selectedPayment.order?.grand_total) }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Pengirim</div>
              <div class="text-white">{{ selectedPayment.sender_name }} ({{ selectedPayment.sender_bank }})</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Nominal Transfer (Diakui)</div>
              <div class="text-white font-bold">{{ formatRupiah(selectedPayment.transferred_amount) }}</div>
            </div>
          </div>
          
          <div class="mb-6">
            <div class="text-white/40 text-sm mb-2">Bukti Transfer</div>
            <div class="bg-dark rounded-xl border border-white/10 p-2 flex justify-center items-center overflow-hidden min-h-[300px]">
              <div v-if="loadingImage" class="text-white/40 animate-pulse">Memuat gambar...</div>
              <img v-else-if="proofImageUrl" :src="proofImageUrl" alt="Bukti Transfer" class="max-w-full h-auto max-h-[500px] object-contain rounded" @error="imageError" />
              <div v-else class="text-white/30 text-sm">Gagal memuat gambar bukti (File mungkin dihapus atau rusak)</div>
            </div>
          </div>
          
          <div v-if="selectedPayment.status === 'pending'" class="space-y-4">
            <div>
              <label class="label-field">Catatan Admin (opsional)</label>
              <textarea v-model="adminNotes" class="input-field" rows="2" placeholder="Alasan penolakan / Catatan internal"></textarea>
            </div>
            <div class="flex gap-4">
              <button @click="rejectPayment" :disabled="processing" class="flex-1 btn-outline border-red-500/50 text-red-400 hover:bg-red-500/10 hover:text-red-300">
                Tolak Pembayaran
              </button>
              <button @click="approvePayment" :disabled="processing" class="flex-1 btn-primary bg-green-600 hover:bg-green-500 text-white border-none">
                Verifikasi & Lunas
              </button>
            </div>
          </div>
          
          <div v-else class="text-center p-4 bg-white/5 rounded-xl border border-white/10">
            <div class="text-white/60 text-sm mb-1">Status saat ini:</div>
            <div :class="statusBadge(selectedPayment.status)" class="badge text-base mb-2 px-4 py-1">{{ statusLabel(selectedPayment.status) }}</div>
            <div v-if="selectedPayment.admin_notes" class="text-white/40 text-sm italic mt-2">
              "{{ selectedPayment.admin_notes }}"
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-white/10 bg-white/[0.02] flex justify-between items-center">
          <button @click="openDeleteModal(selectedPayment)" class="btn-outline border-red-500/40 text-red-400 hover:bg-red-500/10 text-xs px-4 py-2">
            🗑️ Hapus Verifikasi Ini
          </button>
          <button @click="closeModal" class="btn-outline text-xs px-4 py-2">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Modal Password Konfirmasi Hapus Verifikasi Bayar -->
    <div v-if="deletingPayment" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeDeleteModal">
      <div class="card-glass w-full max-w-md rounded-2xl p-6 space-y-4 border border-red-500/30">
        <div class="flex justify-between items-center pb-2 border-b border-white/10">
          <h3 class="font-bold text-red-400 text-lg flex items-center gap-2">
            <span>🗑️</span> Hapus Verifikasi Bayar
          </h3>
          <button @click="closeDeleteModal" class="text-white/50 hover:text-white">✕</button>
        </div>

        <p class="text-xs text-white/70">
          Menghapus verifikasi bayar ini akan mengembalikan status pesanan <strong class="text-electric">{{ deletingPayment.order?.order_code || deletingPayment.deleted_order_code }}</strong> kembali ke <strong>Menunggu Pembayaran</strong>.
        </p>

        <div>
          <label class="label-field">Masukkan Password Admin Anda</label>
          <input 
            v-model="adminPassword" 
            type="password" 
            class="input-field" 
            placeholder="Password admin..." 
            @keyup.enter="confirmDeletePayment"
          />
          <p v-if="deleteError" class="text-xs text-red-400 mt-1.5 font-medium">⚠️ {{ deleteError }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button @click="closeDeleteModal" class="flex-1 btn-outline py-2.5 text-xs">
            Batal
          </button>
          <button 
            @click="confirmDeletePayment" 
            :disabled="processingDelete || !adminPassword" 
            class="flex-1 btn-primary bg-red-600 hover:bg-red-500 text-white border-none py-2.5 text-xs font-bold disabled:opacity-50"
          >
            {{ processingDelete ? 'Menghapus...' : 'Konfirmasi Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const payments = ref<any[]>([])
const loading = ref(true)
const selectedPayment = ref<any>(null)
const adminNotes = ref('')
const processing = ref(false)

const deletingPayment = ref<any>(null)
const adminPassword = ref('')
const processingDelete = ref(false)
const deleteError = ref('')

function openDeleteModal(pay: any) {
  deletingPayment.value = pay
  adminPassword.value = ''
  deleteError.value = ''
}

function closeDeleteModal() {
  deletingPayment.value = null
  adminPassword.value = ''
  deleteError.value = ''
}

async function confirmDeletePayment() {
  if (!adminPassword.value) {
    deleteError.value = 'Password admin wajib diisi.'
    return
  }

  processingDelete.value = true
  deleteError.value = ''
  try {
    const { data } = await api.delete(`/admin/payments/${deletingPayment.value.id}`, {
      data: { password: adminPassword.value }
    })
    alert(data.message || 'Verifikasi bayar berhasil dihapus.')
    closeDeleteModal()
    if (selectedPayment.value) closeModal()
    fetchPayments()
  } catch (e: any) {
    deleteError.value = e.response?.data?.message || 'Password salah atau gagal menghapus.'
  } finally {
    processingDelete.value = false
  }
}

async function fetchPayments() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/payments')
    payments.value = data.data
  } catch (e) {
    console.error('Failed to fetch payments', e)
  } finally {
    loading.value = false
  }
}

const proofImageUrl = ref('')
const loadingImage = ref(false)

async function openModal(pay: any) {
  selectedPayment.value = pay
  adminNotes.value = pay.admin_notes || ''
  proofImageUrl.value = ''
  
  // Fetch image via API to include Bearer token
  loadingImage.value = true
  try {
    const response = await api.get(`/admin/payments/${pay.id}/proof`, {
      responseType: 'blob'
    })
    proofImageUrl.value = URL.createObjectURL(response.data)
  } catch (e) {
    console.error('Gagal memuat gambar', e)
  } finally {
    loadingImage.value = false
  }
}

function closeModal() {
  selectedPayment.value = null
  adminNotes.value = ''
  if (proofImageUrl.value) {
    URL.revokeObjectURL(proofImageUrl.value)
    proofImageUrl.value = ''
  }
}

function imageError(e: Event) {
  const target = e.target as HTMLImageElement
  target.style.display = 'none'
  target.parentElement!.innerHTML = '<div class="text-white/30 text-sm">Gagal memuat gambar bukti (File mungkin dihapus atau rusak)</div>'
}

async function approvePayment() {
  if (!confirm('Anda yakin ingin memverifikasi pembayaran ini? Order akan ditandai LUNAS.')) return
  
  processing.value = true
  try {
    await api.post(`/admin/payments/${selectedPayment.value.id}/approve`, {
      admin_notes: adminNotes.value
    })
    alert('Pembayaran berhasil diverifikasi!')
    closeModal()
    fetchPayments()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Terjadi kesalahan')
  } finally {
    processing.value = false
  }
}

async function rejectPayment() {
  if (!adminNotes.value.trim()) {
    alert('Mohon isi Catatan Admin sebagai alasan penolakan!')
    return
  }
  if (!confirm('Anda yakin ingin MENOLAK pembayaran ini?')) return
  
  processing.value = true
  try {
    await api.post(`/admin/payments/${selectedPayment.value.id}/reject`, {
      rejection_reason: adminNotes.value
    })
    alert('Pembayaran berhasil ditolak.')
    closeModal()
    fetchPayments()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Terjadi kesalahan')
  } finally {
    processing.value = false
  }
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDate(d: string) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

function statusBadge(s: string) {
  const m: Record<string, string> = { 
    pending: 'badge-waiting', 
    verified: 'badge-paid', 
    rejected: 'badge-rejected',
    order_deleted: 'badge-rejected bg-red-500/20 text-red-300 border-red-500/40' 
  }
  return m[s] ?? 'badge'
}

function statusLabel(s: string) {
  const m: Record<string, string> = { 
    pending: 'Menunggu', 
    verified: 'Diverifikasi', 
    rejected: 'Ditolak',
    order_deleted: 'Pesanan Terhapus' 
  }
  return m[s] ?? s
}

onMounted(() => {
  fetchPayments()
})
</script>
