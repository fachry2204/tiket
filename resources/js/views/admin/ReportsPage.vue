<template>
  <div class="p-6 md:p-8">
    <h1 class="text-2xl font-bold text-white mb-6">Laporan & Statistik</h1>
    
    <div v-if="loading" class="card-dark p-8 text-center text-white/40 rounded-xl">
      Memuat laporan penjualan...
    </div>
    
    <div v-else-if="stats" class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card-glass p-6 rounded-xl border border-white/10 relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 text-white/5 text-6xl">💰</div>
          <div class="text-white/40 text-sm mb-2">Total Pendapatan</div>
          <div class="text-2xl font-bold text-accent">{{ formatRupiah(stats.total_revenue) }}</div>
        </div>
        
        <div class="card-glass p-6 rounded-xl border border-white/10 relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 text-white/5 text-6xl">🎫</div>
          <div class="text-white/40 text-sm mb-2">Tiket Terjual</div>
          <div class="text-2xl font-bold text-electric">{{ stats.total_tickets_sold }} <span class="text-sm font-normal text-white/40">tiket</span></div>
        </div>
        
        <div class="card-glass p-6 rounded-xl border border-white/10 relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 text-white/5 text-6xl">✓</div>
          <div class="text-white/40 text-sm mb-2">Check-in</div>
          <div class="text-2xl font-bold text-green-400">
            {{ stats.total_checked_in }} 
            <span class="text-sm font-normal text-white/40">/ {{ stats.total_tickets_sold }}</span>
          </div>
        </div>
        
        <div class="card-glass p-6 rounded-xl border border-white/10 relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 text-white/5 text-6xl">🛒</div>
          <div class="text-white/40 text-sm mb-2">Pesanan Lunas</div>
          <div class="text-2xl font-bold text-white">{{ stats.total_paid_orders }}</div>
        </div>
      </div>
      
      <!-- Breakdown by Ticket Type -->
      <div class="card-dark rounded-xl p-6">
        <h2 class="font-semibold text-white mb-4">Penjualan per Kategori Tiket</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-white/5 text-white/60">
              <tr>
                <th class="px-4 py-3 font-medium">Kategori Tiket</th>
                <th class="px-4 py-3 font-medium text-right">Harga</th>
                <th class="px-4 py-3 font-medium text-center">Terjual / Kuota</th>
                <th class="px-4 py-3 font-medium text-right">Pendapatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="t in stats.sales_by_ticket" :key="t.ticket_name" class="hover:bg-white/[0.02]">
                <td class="px-4 py-3 font-medium text-white">{{ t.ticket_name }}</td>
                <td class="px-4 py-3 text-right text-white/60">{{ formatRupiah(t.price) }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-electric font-bold">{{ t.sold }}</span>
                  <span class="text-white/40 text-xs"> / {{ t.total_quota }}</span>
                  <div class="w-full bg-white/5 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-electric h-full" :style="{ width: `${Math.min(100, (t.sold / t.total_quota) * 100)}%` }"></div>
                  </div>
                </td>
                <td class="px-4 py-3 text-right font-bold text-accent">{{ formatRupiah(t.revenue) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const stats = ref<any>(null)
const loading = ref(true)

async function fetchStats() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/reports/sales')
    stats.value = data.data || data
  } catch (e) {
    console.error('Failed to fetch stats', e)
  } finally {
    loading.value = false
  }
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

onMounted(() => {
  fetchStats()
})
</script>
