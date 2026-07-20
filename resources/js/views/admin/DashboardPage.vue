<template>
  <div class="space-y-6">
    <!-- Stats grid -->
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-pulse">
      <div v-for="i in 8" :key="i" class="card-dark p-5 h-24 rounded-2xl bg-white/5" />
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <StatCard label="Total Order" :value="stats.total_orders" icon="🎫" color="blue" />
      <StatCard label="Order Hari Ini" :value="stats.orders_today" icon="📅" color="purple" />
      <StatCard label="Menunggu Bayar" :value="stats.pending_payment" icon="⏳" color="yellow" />
      <StatCard label="Menunggu Verifikasi" :value="stats.waiting_verification" icon="🔍" color="orange" />
      <StatCard label="Lunas" :value="stats.paid" icon="✅" color="green" />
      <StatCard label="Ditolak" :value="stats.payment_rejected" icon="❌" color="red" />
      <StatCard label="Tiket Terjual" :value="stats.total_tickets_sold" icon="🎟️" color="blue" />
      <StatCard label="Check-in" :value="stats.total_checkins" icon="🚪" color="green" />
    </div>

    <!-- Revenue card -->
    <div class="card-dark p-6 rounded-2xl">
      <div class="text-sm text-white/50 mb-1">Total Pendapatan (Lunas)</div>
      <div class="text-3xl font-bold text-green-400">{{ formatRupiah(stats.total_revenue) }}</div>
    </div>

    <!-- Charts row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Daily sales chart -->
      <div class="card-dark p-6 rounded-2xl">
        <h3 class="text-sm font-semibold text-white/70 mb-4">Penjualan 7 Hari Terakhir</h3>
        <apexchart v-if="chartData.categories.length" type="bar" height="200"
          :options="chartOptions" :series="chartSeries" />
        <div v-else class="h-48 flex items-center justify-center text-white/30 text-sm">Belum ada data penjualan</div>
      </div>

      <!-- Recent orders -->
      <div class="card-dark p-6 rounded-2xl">
        <h3 class="text-sm font-semibold text-white/70 mb-4">Order Terbaru</h3>
        <div class="space-y-3">
          <div v-for="order in stats.recent_orders?.slice(0,5)" :key="order.id"
            class="flex items-center justify-between text-sm py-2 border-b border-white/5 last:border-0">
            <div>
              <div class="font-medium text-white/90">{{ order.customer?.name }}</div>
              <div class="text-white/40 text-xs">{{ order.order_code }}</div>
            </div>
            <span :class="statusBadge(order.order_status)" class="badge text-xs">
              {{ statusLabel(order.order_status) }}
            </span>
          </div>
          <div v-if="!stats.recent_orders?.length" class="text-white/30 text-sm text-center py-4">Belum ada order</div>
        </div>
      </div>
    </div>

    <!-- Pending payments -->
    <div class="card-dark p-6 rounded-2xl">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-white/70">Menunggu Verifikasi Pembayaran</h3>
        <RouterLink to="/admin/payments" class="text-xs text-primary hover:text-primary-light">Lihat Semua →</RouterLink>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-white/40 text-xs border-b border-white/5">
              <th class="text-left pb-2">Order</th>
              <th class="text-left pb-2">Nama</th>
              <th class="text-left pb-2">Bank Pengirim</th>
              <th class="text-right pb-2">Nominal</th>
              <th class="text-left pb-2">Waktu</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in stats.pending_payments" :key="p.id" class="border-b border-white/5 last:border-0">
              <td class="py-2 font-mono text-xs text-electric">{{ p.order?.order_code }}</td>
              <td class="py-2">{{ p.order?.customer?.name }}</td>
              <td class="py-2 text-white/60">{{ p.sender_bank }}</td>
              <td class="py-2 text-right text-green-400">{{ formatRupiah(p.transferred_amount) }}</td>
              <td class="py-2 text-white/40 text-xs">{{ formatDate(p.submitted_at) }}</td>
            </tr>
            <tr v-if="!stats.pending_payments?.length">
              <td colspan="5" class="py-6 text-center text-white/30">Tidak ada pembayaran menunggu verifikasi</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import VueApexCharts from 'vue3-apexcharts'
import api from '@/api'

const apexchart = VueApexCharts

const stats = ref<any>({})
const loading = ref(true)

const chartData = computed(() => {
  const daily = stats.value.daily_sales ?? []
  return {
    categories: daily.map((d: any) => d.date),
    counts: daily.map((d: any) => d.count),
    revenue: daily.map((d: any) => Number(d.revenue)),
  }
})

const chartOptions = computed(() => ({
  chart: { background: 'transparent', toolbar: { show: false } },
  theme: { mode: 'dark' },
  colors: ['#2563EB'],
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
  xaxis: { categories: chartData.value.categories, labels: { style: { colors: '#6b7280', fontSize: '11px' } } },
  yaxis: { labels: { style: { colors: '#6b7280' } } },
  grid: { borderColor: '#ffffff10' },
  tooltip: { theme: 'dark' },
}))

const chartSeries = computed(() => [{ name: 'Order', data: chartData.value.counts }])

async function loadDashboard() {
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDate(d: string) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-'
}

function statusBadge(status: string) {
  const map: Record<string, string> = {
    pending_payment: 'badge-pending', waiting_verification: 'badge-waiting',
    paid: 'badge-paid', payment_rejected: 'badge-rejected',
    expired: 'badge-expired', cancelled: 'badge-cancelled',
  }
  return map[status] ?? 'badge'
}

function statusLabel(status: string) {
  const map: Record<string, string> = {
    pending_payment: 'Menunggu Bayar', waiting_verification: 'Menunggu Verifikasi',
    paid: 'Lunas', payment_rejected: 'Ditolak',
    expired: 'Kedaluwarsa', cancelled: 'Dibatalkan',
  }
  return map[status] ?? status
}
</script>

<script lang="ts">
// Stat card component
const StatCard = {
  props: ['label', 'value', 'icon', 'color'],
  template: `
    <div class="card-dark p-5 rounded-2xl">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-xs text-white/40 mb-1">{{ label }}</div>
          <div class="text-2xl font-bold text-white">{{ value ?? 0 }}</div>
        </div>
        <span class="text-2xl">{{ icon }}</span>
      </div>
    </div>
  `
}
export default { components: { StatCard } }
</script>
