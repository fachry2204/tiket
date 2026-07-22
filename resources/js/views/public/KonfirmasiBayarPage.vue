<template>
  <div class="min-h-screen bg-dark">
    <nav class="bg-dark-card border-b border-white/5 px-4 py-4 flex items-center gap-4">
      <RouterLink to="/" class="text-white/50 hover:text-white text-sm">← Beranda</RouterLink>
      <div class="font-bold text-white">Konfirmasi Bayar</div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-xl">
      <!-- Search form -->
      <div v-if="!order" class="card-glass p-8 mb-6">
        <h1 class="text-xl font-bold text-white mb-2">Cek Status Pembayaran</h1>
        <p class="text-white/50 text-sm mb-6">Masukkan nomor HP atau email yang Anda gunakan saat order</p>

        <form @submit.prevent="searchOrder" class="space-y-4">
          <div>
            <label class="label-field">Nomor HP atau Email</label>
            <input v-model="searchQuery" type="text" class="input-field" placeholder="08xxxxxxxxxx atau email@contoh.com" required />
          </div>
          <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">{{ error }}</div>
          <button type="submit" :disabled="loading" class="btn-primary w-full">
            {{ loading ? 'Mencari...' : '🔍 Cari Order' }}
          </button>
        </form>
      </div>

      <!-- Order found -->
      <div v-else>
        <div class="card-dark p-6 rounded-2xl mb-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <div class="font-mono text-electric text-sm mb-1">{{ order.order_code_full }}</div>
              <div class="font-bold text-white">{{ order.customer_name }}</div>
            </div>
            <span :class="statusBadge(order.order_status)" class="badge">{{ statusLabel(order.order_status) }}</span>
          </div>
          <div class="text-sm text-white/50">{{ formatDateTime(order.created_at) }}</div>
          <div class="text-lg font-bold text-accent mt-2">{{ formatRupiah(order.grand_total) }}</div>
        </div>

        <!-- Upload proof (only if pending or rejected) -->
        <div v-if="order.order_status === 'pending_payment' || order.order_status === 'payment_rejected'" class="card-dark p-6 rounded-2xl mb-6">
          <h2 class="font-semibold text-white mb-4">Upload Bukti Pembayaran</h2>

          <!-- Payment info -->
          <RouterLink :to="`/status-order/${order.order_code_full}`" class="btn-primary w-full text-center block mb-4">
            Lihat Detail & Informasi Rekening
          </RouterLink>

          <form @submit.prevent="uploadProof" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label-field">Nama Pengirim *</label>
                <input v-model="proofForm.sender_name" type="text" class="input-field" required />
              </div>
              <div>
                <label class="label-field">Bank Pengirim *</label>
                <input v-model="proofForm.sender_bank" type="text" class="input-field" placeholder="BCA, BRI, Mandiri..." required />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label-field">Tanggal Transfer *</label>
                <input v-model="proofForm.transfer_date" type="date" class="input-field" required />
              </div>
              <div>
                <label class="label-field">Nominal (Rp) *</label>
                <input v-model="proofForm.transferred_amount" type="number" class="input-field" required />
              </div>
            </div>
            <div>
              <label class="label-field">Bukti Transfer *</label>
              <input type="file" @change="handleFile" accept="image/jpeg,image/png,application/pdf" class="input-field cursor-pointer" required />
              <p class="text-xs text-white/30 mt-1">JPG, PNG, PDF • Maks 5MB</p>
            </div>
            <div>
              <label class="label-field">Catatan (Opsional)</label>
              <textarea v-model="proofForm.notes" class="input-field" rows="2" />
            </div>
            <div v-if="uploadError" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">{{ uploadError }}</div>
            <button type="submit" :disabled="uploading" class="btn-accent w-full">
              {{ uploading ? 'Mengunggah...' : '📤 Upload Bukti Bayar' }}
            </button>
          </form>
        </div>

        <div v-else-if="order.order_status === 'paid'" class="card-dark p-6 rounded-2xl mb-6 text-center">
          <div class="text-4xl mb-3">✅</div>
          <div class="font-bold text-green-400 text-lg">Pembayaran Diterima!</div>
          <p class="text-white/50 text-sm mt-2">Pembayaran anda sudah kami terima, Mohon klik tombol lanjutkan untuk melihat data pesanan anda.</p>
          <RouterLink :to="`/status-order/${order.order_code_full}`" class="btn-primary mt-4 inline-block">
            Lihat Pesanan
          </RouterLink>
        </div>

        <div v-else-if="order.order_status === 'waiting_verification'" class="card-dark p-6 rounded-2xl mb-6 text-center">
          <div class="text-4xl mb-3">🔍</div>
          <div class="font-bold text-blue-400 text-lg">Sedang Diverifikasi</div>
          <p class="text-white/50 text-sm mt-2">Pembayaran Anda sedang diperiksa admin. Proses 1×24 jam.</p>
        </div>

        <button @click="order = null; searchQuery = ''" class="btn-outline w-full text-sm">← Cari Order Lain</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/api'

const route = useRoute()
const searchQuery = ref('')
const order = ref<any>(null)
const loading = ref(false)
const error = ref('')
const uploading = ref(false)
const uploadError = ref('')
const proofForm = ref({ sender_name: '', sender_bank: '', transfer_date: '', transferred_amount: '', notes: '' })
const proofFile = ref<File | null>(null)

function handleFile(e: Event) {
  const input = e.target as HTMLInputElement
  proofFile.value = input.files?.[0] ?? null
}

async function searchOrder() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.post('/orders/search', { search: searchQuery.value })
    order.value = data.data
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Order tidak ditemukan'
  } finally {
    loading.value = false
  }
}

async function uploadProof() {
  if (!proofFile.value) return
  uploading.value = true
  uploadError.value = ''
  const fd = new FormData()
  Object.entries(proofForm.value).forEach(([k, v]) => fd.append(k, v))
  fd.append('proof', proofFile.value)
  try {
    await api.post(`/orders/${order.value.order_code_full}/payment-confirmations`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    order.value.order_status = 'waiting_verification'
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const firstError = Object.values(e.response.data.errors)[0] as string[]
      uploadError.value = firstError[0]
    } else {
      uploadError.value = e.response?.data?.message ?? 'Upload gagal'
    }
  } finally {
    uploading.value = false
  }
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
}

function statusBadge(s: string) {
  const m: Record<string, string> = { pending_payment: 'badge-pending', waiting_verification: 'badge-waiting', paid: 'badge-paid', payment_rejected: 'badge-rejected', expired: 'badge-expired' }
  return m[s] ?? 'badge'
}

function statusLabel(s: string) {
  const m: Record<string, string> = { pending_payment: 'Menunggu Bayar', waiting_verification: 'Menunggu Verifikasi', paid: 'Lunas', payment_rejected: 'Ditolak', expired: 'Kedaluwarsa' }
  return m[s] ?? s
}

onMounted(() => {
  if (route.query.search) {
    searchQuery.value = route.query.search as string
    searchOrder()
  }
})
</script>
