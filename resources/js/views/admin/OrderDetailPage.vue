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
            <div class="flex items-center gap-2">
              <span :class="statusBadge(order.order_status)" class="badge text-sm px-3 py-1">{{ statusLabel(order.order_status) }}</span>
              <span v-if="order.e_tickets?.length > 0" class="bg-green-500/20 text-green-400 border border-green-500/30 text-xs px-3 py-1 rounded-full font-medium">
                ✅ Tiket Terunggah
              </span>
              <span v-else class="bg-white/5 text-white/40 border border-white/10 text-xs px-3 py-1 rounded-full">
                ⏳ Tiket Belum Diupload
              </span>
            </div>
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
              <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm text-center font-medium">
                ✅ Pesanan Sudah Lunas
              </div>
              
              <!-- Section 1: Upload E-Ticket -->
              <div class="border-t border-white/10 pt-4">
                <h3 class="font-bold text-white mb-1 text-sm">Upload E-Ticket (PDF / Gambar)</h3>
                <p class="text-xs text-white/40 mb-3">Pilih file e-ticket fisik untuk pemesan.</p>
                <input 
                  type="file" 
                  multiple 
                  accept=".pdf,image/*"
                  @change="(e) => eTicketFiles = (e.target as HTMLInputElement).files"
                  class="block w-full text-xs text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 mb-3 cursor-pointer"
                />
                <button 
                  v-if="eTicketFiles?.length"
                  @click="uploadETickets" 
                  :disabled="uploading" 
                  class="btn-outline w-full py-2 text-xs mb-3 flex items-center justify-center gap-2"
                >
                  <span>⬆️</span>
                  <span>{{ uploading ? 'Mengunggah...' : 'Upload Berkas Saja' }}</span>
                </button>
              </div>
              
              <!-- Section 2: List Berkas E-Ticket Terunggah -->
              <div v-if="order.e_tickets?.length" class="border-t border-white/10 pt-4 space-y-2">
                <h3 class="font-bold text-white mb-2 text-sm">File Tiket Terunggah ({{ order.e_tickets.length }}):</h3>
                <div v-for="file in order.e_tickets" :key="file.id" class="flex items-center justify-between bg-white/5 p-3 rounded-lg text-sm border border-white/5">
                  <div class="truncate flex-1 pr-4 text-white/80" :title="file.file_name">
                    📄 {{ file.file_name }}
                  </div>
                  <div class="flex gap-3">
                    <a :href="getDownloadUrl(file.id)" target="_blank" class="text-blue-400 hover:text-blue-300 font-bold" title="Download">⬇️</a>
                    <button @click="deleteETicket(file.id)" class="text-red-400 hover:text-red-300" title="Hapus">🗑️</button>
                  </div>
                </div>
              </div>

              <!-- Section 3: Tombol Kirim Tiket (Posisi di Bawah Upload Tiket) -->
              <div class="border-t border-white/10 pt-4 space-y-2">
                <button 
                  @click="saveAndSendTicket" 
                  :disabled="processingSend || (!order.e_tickets?.length && !eTicketFiles?.length)" 
                  class="w-full btn-primary py-3 flex items-center justify-center gap-2 text-sm shadow-lg shadow-primary/20 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:opacity-40"
                >
                  <span>📩</span>
                  <span>{{ processingSend ? 'Proses Mengunggah & Mengirim...' : 'Simpan & Kirim Tiket ke Email & WA' }}</span>
                </button>

                <p v-if="!order.e_tickets?.length && !eTicketFiles?.length" class="text-[11px] text-yellow-400/90 text-center font-medium bg-yellow-500/10 p-2 rounded-lg border border-yellow-500/20">
                  ⚠️ Upload berkas e-ticket terlebih dahulu untuk mengirimkan tiket ke pemesan.
                </p>
                <p v-else-if="eTicketFiles?.length && !order.e_tickets?.length" class="text-[11px] text-green-400 text-center font-medium bg-green-500/10 p-2 rounded-lg border border-green-500/20">
                  💡 Mengeklik tombol di atas akan menyimpan file yang dipilih dan langsung mengirimi user.
                </p>
              </div>
            </div>

            <div v-else-if="order.order_status !== 'cancelled'" class="p-4 bg-yellow-500/10 border border-yellow-500/20 text-yellow-300 rounded-xl text-xs space-y-1.5 text-center mt-3">
              <div class="font-bold text-sm text-yellow-200">🔒 Upload & Kirim Tiket Terkunci</div>
              <p class="text-white/60">Upload E-Ticket dan Pengiriman Tiket hanya dapat dilakukan apabila status pembayaran pesanan ini sudah <strong>LUNAS</strong>.</p>
            </div>

            <div v-if="order.order_status === 'cancelled'" class="text-center p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm">
              Pesanan ini telah dibatalkan.
            </div>
            <button 
              @click="openDeleteModal" 
              class="w-full btn-outline border-red-500/50 text-red-400 hover:bg-red-500/10 hover:text-red-300 py-2.5 text-xs font-semibold flex items-center justify-center gap-2 mt-4"
            >
              <span>🗑️</span> Hapus Pesanan Ini
            </button>
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

    <!-- Modal Password Konfirmasi Hapus Order -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeDeleteModal">
      <div class="card-glass w-full max-w-md rounded-2xl p-6 space-y-4 border border-red-500/30">
        <div class="flex justify-between items-center pb-2 border-b border-white/10">
          <h3 class="font-bold text-red-400 text-lg flex items-center gap-2">
            <span>🗑️</span> Hapus Pesanan Ini
          </h3>
          <button @click="closeDeleteModal" class="text-white/50 hover:text-white">✕</button>
        </div>

        <p class="text-xs text-white/70">
          Anda yakin ingin menghapus pesanan <strong class="text-electric">{{ order.order_code }}</strong> ({{ order.customer?.name }})? Data verifikasi bayar akan tetap tersimpan dengan status <strong>Pesanan Terhapus</strong>.
        </p>

        <div>
          <label class="label-field">Masukkan Password Admin Anda</label>
          <input 
            v-model="adminPassword" 
            type="password" 
            class="input-field" 
            placeholder="Password admin..." 
            @keyup.enter="confirmDeleteOrder"
          />
          <p v-if="deleteError" class="text-xs text-red-400 mt-1.5 font-medium">⚠️ {{ deleteError }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button @click="closeDeleteModal" class="flex-1 btn-outline py-2.5 text-xs">
            Batal
          </button>
          <button 
            @click="confirmDeleteOrder" 
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
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/api'

const route = useRoute()
const router = useRouter()
const order = ref<any>(null)
const loading = ref(true)
const processing = ref(false)
const uploading = ref(false)
const processingSend = ref(false)
const eTicketFiles = ref<FileList | null>(null)

const showDeleteModal = ref(false)
const adminPassword = ref('')
const processingDelete = ref(false)
const deleteError = ref('')

function openDeleteModal() {
  showDeleteModal.value = true
  adminPassword.value = ''
  deleteError.value = ''
}

function closeDeleteModal() {
  showDeleteModal.value = false
  adminPassword.value = ''
  deleteError.value = ''
}

async function confirmDeleteOrder() {
  if (!adminPassword.value) {
    deleteError.value = 'Password admin wajib diisi.'
    return
  }

  processingDelete.value = true
  deleteError.value = ''
  try {
    const { data } = await api.delete(`/admin/orders/${order.value.id}`, {
      data: { password: adminPassword.value }
    })
    alert(data.message || 'Pesanan berhasil dihapus.')
    closeDeleteModal()
    router.push('/admin/orders')
  } catch (e: any) {
    deleteError.value = e.response?.data?.message || 'Password salah atau gagal menghapus pesanan.'
  } finally {
    processingDelete.value = false
  }
}

async function saveAndSendTicket() {
  // 1. If files are selected in file input, upload them first
  if (eTicketFiles.value && eTicketFiles.value.length > 0) {
    uploading.value = true
    const formData = new FormData()
    for (let i = 0; i < eTicketFiles.value.length; i++) {
      formData.append('files[]', eTicketFiles.value[i])
    }
    try {
      await api.post(`/admin/orders/${order.value.id}/e-tickets`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      eTicketFiles.value = null
      const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement
      if (fileInput) fileInput.value = ''
      await fetchOrder()
    } catch (e: any) {
      alert(e.response?.data?.message || 'Gagal mengunggah berkas e-ticket')
      uploading.value = false
      return
    } finally {
      uploading.value = false
    }
  }

  // 2. Ensure order has uploaded tickets
  if (!order.value.e_tickets || order.value.e_tickets.length === 0) {
    alert('Upload file e-ticket terlebih dahulu sebelum mengirim tiket ke pemesan!')
    return
  }

  // 3. Confirm & Send to Email + WA
  if (!confirm(`Simpan & Kirim e-tiket pesanan ${order.value.order_code} ke Email (${order.value.customer?.email}) dan WhatsApp (${order.value.customer?.phone})?`)) return

  processingSend.value = true
  try {
    const { data } = await api.post(`/admin/orders/${order.value.id}/resend-ticket`)
    alert(data.message || 'Tiket berhasil disimpan dan dikirim ke Email & WA pemesan!')
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengirim tiket.')
  } finally {
    processingSend.value = false
  }
}

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
