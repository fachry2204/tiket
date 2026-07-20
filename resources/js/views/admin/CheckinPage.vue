<template>
  <div class="p-6 md:p-8">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold text-white mb-2 text-center">Scan / Verifikasi Tiket</h1>
      <p class="text-white/40 text-center mb-8">Scan QR Code atau masukkan kode tiket secara manual untuk check-in pengunjung.</p>
      
      <div class="card-dark rounded-xl p-6 md:p-8 mb-6">
        <form @submit.prevent="verifyTicket" class="flex flex-col md:flex-row gap-4">
          <button type="button" @click="startScanner" class="btn-outline py-4 px-6 flex items-center justify-center gap-2 md:w-auto w-full text-white hover:text-white/80 transition-colors">
            <span class="text-2xl">📷</span> 
            <span class="font-bold">Kamera</span>
          </button>
          <div class="flex-1 relative">
            <input 
              v-model="ticketCode" 
              type="text" 
              class="input-field text-xl font-mono text-center tracking-widest uppercase py-4" 
              placeholder="MASUKKAN KODE" 
              required
              autofocus
            />
          </div>
          <button type="submit" :disabled="loading" class="btn-primary py-4 px-8 md:w-auto w-full font-bold text-lg">
            {{ loading ? 'Memeriksa...' : 'Cek Tiket' }}
          </button>
        </form>
      </div>
      
      <!-- Hasil Verifikasi -->
      <div v-if="result" class="card-glass rounded-xl overflow-hidden border-2" :class="result.is_valid ? 'border-green-500/50' : 'border-red-500/50'">
        <div class="p-6" :class="result.is_valid ? 'bg-green-500/10' : 'bg-red-500/10'">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" :class="result.is_valid ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
              {{ result.is_valid ? '✓' : '✕' }}
            </div>
            <div>
              <h2 class="text-xl font-bold" :class="result.is_valid ? 'text-green-400' : 'text-red-400'">
                {{ result.message }}
              </h2>
              <div class="font-mono text-white/60 text-sm mt-1">{{ result.ticket_code }}</div>
            </div>
          </div>
          
          <div v-if="result.type === 'ticket' && result.ticket" class="bg-dark/50 rounded-lg p-4 grid grid-cols-2 gap-4 text-sm mt-4">
            <div>
              <div class="text-white/40 mb-1">Kategori Tiket</div>
              <div class="font-bold text-white">{{ result.ticket.ticket_name }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Nama Pemesan</div>
              <div class="font-bold text-white">{{ result.ticket.customer_name }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Kode Order</div>
              <div class="font-mono text-electric">{{ result.ticket.order_code }}</div>
            </div>
            <div v-if="result.ticket.check_in_time">
              <div class="text-white/40 mb-1">Waktu Check-in</div>
              <div class="text-white font-medium">{{ formatDateTime(result.ticket.check_in_time) }}</div>
            </div>
          </div>
          
          <div v-else-if="result.type === 'order' && result.order" class="bg-dark/50 rounded-lg p-4 grid grid-cols-2 gap-4 text-sm mt-4">
            <div>
              <div class="text-white/40 mb-1">Tanggal Pesan</div>
              <div class="font-medium text-white">{{ formatDateTime(result.order.order_date) }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Status Bayar</div>
              <div class="font-bold uppercase text-accent">{{ result.order.status }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Nama Pemesan</div>
              <div class="font-bold text-white">{{ result.order.customer_name }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">No HP</div>
              <div class="font-medium text-white">{{ result.order.customer_phone }}</div>
            </div>
            <div>
              <div class="text-white/40 mb-1">Jumlah Tiket</div>
              <div class="font-bold text-white">{{ result.order.ticket_count }} Tiket</div>
            </div>
          </div>
          
          <div v-if="result.is_valid && result.type === 'ticket' && !result.ticket?.check_in_time" class="mt-6">
            <button @click="processCheckin" :disabled="processingCheckin" class="w-full btn-primary bg-green-600 hover:bg-green-500 border-none py-3 text-lg font-bold">
              {{ processingCheckin ? 'Memproses...' : 'Proses Check-in Sekarang' }}
            </button>
          </div>
          
          <div v-if="result.is_valid && result.type === 'order'" class="mt-6">
            <RouterLink :to="`/admin/orders/${result.order?.order_id}`" class="w-full btn-primary flex justify-center py-3 text-lg font-bold">
              Lihat Detail Pesanan & Penukaran
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Camera Scanner Modal -->
    <div v-if="showScanner" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
      <div class="card-glass w-full max-w-lg rounded-2xl overflow-hidden flex flex-col">
        <div class="p-6 border-b border-white/10 flex justify-between items-center bg-dark">
          <h2 class="text-xl font-bold text-white">Scan QR Code</h2>
          <button @click="stopScanner" class="text-white/50 hover:text-white text-xl">✕</button>
        </div>
        <div class="p-4 bg-black relative flex flex-col items-center justify-center min-h-[300px]">
          <div v-if="scanError" class="text-red-400 text-center mb-4 p-4 bg-red-500/10 rounded-xl border border-red-500/20">
            {{ scanError }}
          </div>
          <div id="reader" class="w-full max-w-sm mx-auto overflow-hidden rounded-xl border-2 border-primary/50"></div>
          <p class="text-white/50 text-sm mt-6 text-center">Arahkan kamera ke QR Code tiket pengunjung.</p>
        </div>
        <div class="p-4 bg-dark border-t border-white/10">
          <button @click="stopScanner" class="btn-outline w-full py-3">Tutup Kamera</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import api from '@/api'
import { Html5Qrcode } from 'html5-qrcode'

const ticketCode = ref('')
const loading = ref(false)
const processingCheckin = ref(false)
const result = ref<any>(null)
const showScanner = ref(false)
const scanError = ref('')
let html5QrCode: Html5Qrcode | null = null

async function startScanner() {
  showScanner.value = true
  scanError.value = ''
  setTimeout(async () => {
    try {
      html5QrCode = new Html5Qrcode("reader")
      await html5QrCode.start(
        { facingMode: "environment" }, 
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
          ticketCode.value = decodedText
          stopScanner()
          verifyTicket()
        },
        undefined
      )
    } catch (err: any) {
      scanError.value = 'Gagal mengakses kamera. Pastikan browser memiliki izin kamera.'
      console.error(err)
    }
  }, 200)
}

async function stopScanner() {
  if (html5QrCode && html5QrCode.isScanning) {
    await html5QrCode.stop().catch(console.error)
    html5QrCode.clear()
    html5QrCode = null
  }
  showScanner.value = false
}

onUnmounted(() => {
  if (html5QrCode && html5QrCode.isScanning) {
    html5QrCode.stop().catch(console.error)
  }
})

async function verifyTicket() {
  if (!ticketCode.value) return
  loading.value = true
  result.value = null
  try {
    const { data } = await api.get('/admin/check-in/verify', {
      params: { ticket_code: ticketCode.value.toUpperCase() }
    })
    result.value = {
      is_valid: data.valid,
      type: data.type,
      message: data.valid ? (data.type === 'order' ? 'Pesanan Valid' : 'Tiket Valid') : 'Kode Tidak Valid',
      ticket_code: data.type === 'ticket' ? data.data?.ticket_code : data.data?.order_code,
      ticket: data.type === 'ticket' && data.data ? {
        ticket_name: data.data.orderItem?.ticketProduct?.name,
        customer_name: data.data.order?.customer?.name,
        order_code: data.data.order?.order_code,
        check_in_time: data.data.check_in_time
      } : null,
      order: data.type === 'order' && data.data ? {
        order_id: data.data.order_id,
        order_date: data.data.order_date,
        order_code: data.data.order_code,
        customer_name: data.data.customer_name,
        customer_phone: data.data.customer_phone,
        ticket_count: data.data.ticket_count,
        status: data.data.status
      } : null
    }
  } catch (e: any) {
    result.value = { 
      is_valid: false, 
      message: e.response?.data?.message || 'Terjadi kesalahan sistem' 
    }
  } finally {
    loading.value = false
  }
}

async function processCheckin() {
  if (!result.value?.ticket_code) return
  processingCheckin.value = true
  try {
    await api.post('/admin/check-in', {
      ticket_code: result.value.ticket_code
    })
    alert('Check-in Berhasil!')
    ticketCode.value = ''
    result.value = null
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal memproses check-in')
  } finally {
    processingCheckin.value = false
  }
}

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
}
</script>
