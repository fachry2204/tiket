<template>
  <div class="p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
      <h1 class="text-2xl font-bold text-white">Daftar Pesanan</h1>
      
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select v-model="filterStatus" class="input-field max-w-[150px]">
          <option value="">Semua Status</option>
          <option value="pending_payment">Menunggu Bayar</option>
          <option value="waiting_verification">Menunggu Verifikasi</option>
          <option value="paid">Lunas</option>
          <option value="payment_rejected">Ditolak</option>
          <option value="expired">Kedaluwarsa</option>
        </select>
        
        <input 
          v-model="search" 
          type="text" 
          placeholder="Cari order, nama, hp..." 
          class="input-field flex-1 md:w-64"
        />
      </div>
    </div>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat daftar pesanan...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="filteredOrders.length === 0" class="p-8 text-center text-white/40">
        Tidak ada data pesanan yang ditemukan.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-4 py-4 font-medium">Tanggal Pesan</th>
              <th class="px-4 py-4 font-medium">No Pesanan</th>
              <th class="px-4 py-4 font-medium">Nama</th>
              <th class="px-4 py-4 font-medium">No HP</th>
              <th class="px-4 py-4 font-medium text-center">Jumlah Tiket</th>
              <th class="px-4 py-4 font-medium text-right">Total Harga</th>
              <th class="px-4 py-4 font-medium text-center">Status Bayar</th>
              <th class="px-4 py-4 font-medium text-center">Status Tiket</th>
              <th class="px-4 py-4 font-medium text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-4 py-4 text-white/80 whitespace-nowrap">{{ formatDateTime(order.created_at) }}</td>
              <td class="px-4 py-4">
                <div class="font-mono text-electric font-bold">{{ order.order_code }}</div>
              </td>
              <td class="px-4 py-4 text-white">{{ order.customer?.name }}</td>
              <td class="px-4 py-4 text-white/80">{{ order.customer?.phone }}</td>
              <td class="px-4 py-4 text-center font-bold text-white">{{ ticketCount(order) }}</td>
              <td class="px-4 py-4 text-right font-bold text-accent whitespace-nowrap">{{ formatRupiah(order.grand_total) }}</td>
              <td class="px-4 py-4 text-center">
                <span :class="statusBadge(order.order_status)" class="badge whitespace-nowrap">{{ statusLabel(order.order_status) }}</span>
              </td>
              <td class="px-4 py-4 text-center whitespace-nowrap">
                <span v-if="(order.e_tickets?.length || order.e_tickets_count) > 0" class="bg-green-500/20 text-green-400 border border-green-500/30 px-2.5 py-1 rounded-full text-xs font-medium inline-block">
                  ✅ Sudah Upload
                </span>
                <span v-else class="bg-white/5 text-white/40 border border-white/10 px-2.5 py-1 rounded-full text-xs inline-block">
                  ⏳ Belum Upload
                </span>
              </td>
              <td class="px-4 py-4 text-center space-x-2 whitespace-nowrap">
                <RouterLink :to="`/admin/orders/${order.id}`" class="btn-outline text-xs px-3 py-1">Detail</RouterLink>
                <button @click="openDeleteModal(order)" class="btn-outline border-red-500/40 text-red-400 hover:bg-red-500/10 text-xs px-3 py-1">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Password Konfirmasi Hapus Order -->
    <div v-if="deletingOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeDeleteModal">
      <div class="card-glass w-full max-w-md rounded-2xl p-6 space-y-4 border border-red-500/30">
        <div class="flex justify-between items-center pb-2 border-b border-white/10">
          <h3 class="font-bold text-red-400 text-lg flex items-center gap-2">
            <span>🗑️</span> Hapus Pesanan
          </h3>
          <button @click="closeDeleteModal" class="text-white/50 hover:text-white">✕</button>
        </div>

        <p class="text-xs text-white/70">
          Anda yakin ingin menghapus pesanan <strong class="text-electric">{{ deletingOrder.order_code }}</strong> ({{ deletingOrder.customer?.name }})? Data verifikasi bayar akan tetap tersimpan dengan status <strong>Pesanan Terhapus</strong>.
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
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api'

const orders = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')

const deletingOrder = ref<any>(null)
const adminPassword = ref('')
const processingDelete = ref(false)
const deleteError = ref('')

function openDeleteModal(order: any) {
  deletingOrder.value = order
  adminPassword.value = ''
  deleteError.value = ''
}

function closeDeleteModal() {
  deletingOrder.value = null
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
    const { data } = await api.delete(`/admin/orders/${deletingOrder.value.id}`, {
      data: { password: adminPassword.value }
    })
    alert(data.message || 'Pesanan berhasil dihapus.')
    closeDeleteModal()
    fetchOrders()
  } catch (e: any) {
    deleteError.value = e.response?.data?.message || 'Password salah atau gagal menghapus pesanan.'
  } finally {
    processingDelete.value = false
  }
}

async function fetchOrders() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/orders')
    orders.value = data.data || data
    // Handle pagination structure if data is paginated
    if (data.data?.data) {
        orders.value = data.data.data
    }
  } catch (e) {
    console.error('Failed to fetch orders', e)
  } finally {
    loading.value = false
  }
}

const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    // Filter status
    if (filterStatus.value && o.order_status !== filterStatus.value) return false
    
    // Filter search
    if (search.value) {
      const q = search.value.toLowerCase()
      const matchCode = o.order_code?.toLowerCase().includes(q)
      const matchName = o.customer?.name?.toLowerCase().includes(q)
      const matchPhone = o.customer?.phone?.toLowerCase().includes(q)
      const matchEmail = o.customer?.email?.toLowerCase().includes(q)
      if (!matchCode && !matchName && !matchPhone && !matchEmail) return false
    }
    
    return true
  })
})

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function ticketCount(order: any) {
  if (!order.items) return 0
  return order.items.reduce((acc: number, cur: any) => acc + (cur.quantity || 0), 0)
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
  fetchOrders()
})
</script>
