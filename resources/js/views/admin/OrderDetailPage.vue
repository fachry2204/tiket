<template>
  <div class="p-6 md:p-8">
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/orders" class="text-white/50 hover:text-white transition-colors">← Kembali</RouterLink>
      <h1 class="text-2xl font-bold text-white">Detail Pesanan</h1>
    </div>
    
    <div v-if="loading" class="card-dark p-8 text-center text-white/40 rounded-xl">
      Memuat detail pesanan...
    </div>
    
    <div v-else-if="!order" class="card-dark p-8 text-center text-white/40 rounded-xl">
      Pesanan tidak ditemukan.
    </div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Kolom Kiri: Detail Pesanan -->
      <div class="lg:col-span-2 space-y-6">
        <div class="card-dark rounded-xl p-6">
          <div class="flex justify-between items-start mb-6">
            <div>
              <div class="font-mono text-electric text-xl font-bold">{{ order.order_code }}</div>
              <div class="text-white/40 text-sm mt-1">{{ formatDateTime(order.created_at) }}</div>
            </div>
            <span :class="statusBadge(order.order_status)" class="badge text-sm px-3 py-1">{{ statusLabel(order.order_status) }}</span>
          </div>
          
          <div class="border-t border-white/5 pt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
              <div class="text-white/40 mb-1">Nama Pemesan</div>
              <div class="font-medium text-white">{{ order.customer?.name }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Nomor HP</div>
              <div class="font-medium text-white">{{ order.customer?.phone }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Email</div>
              <div class="font-medium text-white">{{ order.customer?.email }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">NIK / Identitas</div>
              <div class="font-medium text-white">{{ order.customer?.identity_number || '-' }}</div>
            </div>
          </div>
        </div>
        
        <div class="card-dark rounded-xl p-6">
          <h2 class="font-semibold text-white mb-4">Daftar Tiket</h2>
          <div class="space-y-4">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center bg-white/5 p-4 rounded-lg">
              <div>
                <div class="font-medium text-white">{{ item.ticket_name }}</div>
                <div class="text-sm text-white/40">1 tiket</div>
              </div>
              <div class="font-bold text-accent">{{ formatRupiah(item.price_snapshot) }}</div>
            </div>
            
            <div class="border-t border-white/10 pt-4 space-y-2">
              <div class="flex justify-between text-white/60 text-sm">
                <span>Subtotal Tiket</span>
                <span>{{ formatRupiah(order.total_amount) }}</span>
              </div>
              <div class="flex justify-between text-white/60 text-sm">
                <span>Kode Unik</span>
                <span class="text-electric">+ {{ formatRupiah(order.unique_code) }}</span>
              </div>
              <div class="flex justify-between text-white font-bold text-lg pt-2 border-t border-white/10">
                <span>Total Bayar</span>
                <span class="text-accent">{{ formatRupiah(order.grand_total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Kolom Kanan: Aksi & Info Pembayaran -->
      <div class="space-y-6">
        <div class="card-dark rounded-xl p-6">
          <h2 class="font-semibold text-white mb-4">Aksi Admin</h2>
          <div class="space-y-3">
            <button 
              v-if="['pending_payment', 'payment_rejected'].includes(order.order_status)" 
              @click="extendExpiry" 
              :disabled="processing"
              class="w-full btn-outline py-3 flex items-center justify-center gap-2"
            >
              ⏳ Perpanjang Waktu Bayar
            </button>
            <button 
              v-if="!['paid', 'cancelled'].includes(order.order_status)" 
              @click="cancelOrder" 
              :disabled="processing"
              class="w-full btn-outline border-red-500/50 text-red-400 hover:bg-red-500/10 hover:text-red-300 py-3"
            >
              ❌ Batalkan Pesanan
            </button>
            <div v-if="order.order_status === 'paid'" class="space-y-4">
              <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm text-center">
                Pesanan sudah lunas.
              </div>
              
              <div class="border-t border-white/10 pt-4">
                <h3 class="font-bold text-white mb-3 text-sm">Upload E-Ticket (Multi)</h3>
                <input 
                  type="file" 
                  multiple 
                  accept=".pdf,image/*"
                  @change="(e) => eTicketFiles = (e.target as HTMLInputElement).files"
                  class="block w-full text-xs text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 mb-3 cursor-pointer"
                />
                <button 
                  @click="uploadETickets" 
                  :disabled="uploading || !eTicketFiles?.length" 
                  class="btn-primary w-full py-2 text-sm"
                >
                  {{ uploading ? 'Mengunggah...' : 'Upload File' }}
                </button>
              </div>
              
              <div v-if="order.e_tickets?.length" class="border-t border-white/10 pt-4 space-y-2">
                <h3 class="font-bold text-white mb-2 text-sm">File Terunggah:</h3>
                <div v-for="file in order.e_tickets" :key="file.id" class="flex items-center justify-between bg-white/5 p-3 rounded-lg text-sm">
                  <div class="truncate flex-1 pr-4 text-white/80" :title="file.file_name">
                    {{ file.file_name }}
                  </div>
                  <div class="flex gap-3">
                    <a :href="getDownloadUrl(file.id)" target="_blank" class="text-blue-400 hover:text-blue-300" title="Download">⬇️</a>
                    <button @click="deleteETicket(file.id)" class="text-red-400 hover:text-red-300" title="Hapus">🗑️</button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="order.order_status === 'cancelled'" class="text-center p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm">
              Pesanan ini telah dibatalkan.
            </div>
          </div>
        </div>
        
        <div class="card-dark rounded-xl p-6">
          <h2 class="font-semibold text-white mb-4">Batas Waktu Pembayaran</h2>
          <div class="bg-white/5 rounded-lg p-4 text-center">
            <div class="font-mono text-lg font-bold text-electric mb-1">{{ formatDateTime(order.expires_at) }}</div>
            <div class="text-xs text-white/50">Otomatis batal jika melewati batas ini</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api'

const route = useRoute()
const order = ref<any>(null)
const loading = ref(true)
const processing = ref(false)
const uploading = ref(false)
const eTicketFiles = ref<FileList | null>(null)

async function fetchOrder() {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/orders/${route.params.id}`)
    order.value = data.data
  } catch (e) {
    console.error('Failed to fetch order', e)
  } finally {
    loading.value = false
  }
}

async function cancelOrder() {
  if (!confirm('Anda yakin ingin membatalkan pesanan ini secara manual?')) return
  
  processing.value = true
  try {
    await api.post(`/admin/orders/${order.value.id}/cancel`)
    alert('Pesanan berhasil dibatalkan.')
    fetchOrder()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal membatalkan pesanan')
  } finally {
    processing.value = false
  }
}

async function extendExpiry() {
  if (!confirm('Anda yakin ingin memperpanjang waktu pembayaran (+2 jam)?')) return
  
  processing.value = true
  try {
    await api.post(`/admin/orders/${order.value.id}/extend-expiry`)
    alert('Waktu pembayaran berhasil diperpanjang.')
    fetchOrder()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal memperpanjang waktu')
  } finally {
    processing.value = false
  }
}

async function uploadETickets() {
  if (!eTicketFiles.value || eTicketFiles.value.length === 0) return
  
  uploading.value = true
  const formData = new FormData()
  for (let i = 0; i < eTicketFiles.value.length; i++) {
    formData.append('files[]', eTicketFiles.value[i])
  }
  
  try {
    await api.post(`/admin/orders/${order.value.id}/e-tickets`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert('E-Ticket berhasil diunggah')
    eTicketFiles.value = null
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement
    if (fileInput) fileInput.value = ''
    fetchOrder()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengunggah e-ticket')
  } finally {
    uploading.value = false
  }
}

async function deleteETicket(id: number) {
  if (!confirm('Hapus file e-ticket ini?')) return
  try {
    await api.delete(`/admin/orders/e-tickets/${id}`)
    fetchOrder()
  } catch (e) {
    alert('Gagal menghapus e-ticket')
  }
}

function getDownloadUrl(id: number) {
  return `${api.defaults.baseURL}/admin/orders/e-tickets/${id}/download`
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
}

function statusBadge(s: string) {
  const m: Record<string, string> = { pending_payment: 'badge-pending', waiting_verification: 'badge-waiting', paid: 'badge-paid', payment_rejected: 'badge-rejected', expired: 'badge-expired', cancelled: 'badge-rejected' }
  return m[s] ?? 'badge'
}

function statusLabel(s: string) {
  const m: Record<string, string> = { pending_payment: 'Menunggu Bayar', waiting_verification: 'Menunggu Verif', paid: 'Lunas', payment_rejected: 'Ditolak', expired: 'Kedaluwarsa', cancelled: 'Dibatalkan' }
  return m[s] ?? s
}

onMounted(() => {
  fetchOrder()
})
</script>
