<template>
  <div class="p-6 md:p-8">
    <h1 class="text-2xl font-bold text-white mb-6">Log Aktivitas (Audit)</h1>
    
    <div class="card-dark rounded-xl overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center text-white/40">
        Memuat log aktivitas...
      </div>
      
      <!-- Empty State -->
      <div v-else-if="logs.length === 0" class="p-8 text-center text-white/40">
        Belum ada log aktivitas yang tercatat.
      </div>
      
      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-white/5 text-white/60">
            <tr>
              <th class="px-6 py-4 font-medium">Waktu</th>
              <th class="px-6 py-4 font-medium">User (Admin)</th>
              <th class="px-6 py-4 font-medium">Aksi</th>
              <th class="px-6 py-4 font-medium">Target</th>
              <th class="px-6 py-4 font-medium">IP & User Agent</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 text-white/60 whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
              <td class="px-6 py-4">
                <div class="font-bold text-white">{{ log.user?.name || 'Sistem' }}</div>
                <div class="text-xs text-white/40">{{ log.user?.email || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 bg-white/5 text-electric rounded text-xs font-mono border border-white/10">{{ log.action }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="text-white">{{ log.model_type }}</div>
                <div class="text-xs text-white/40 font-mono">ID: {{ log.model_id }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-white/80 text-xs">{{ log.ip_address }}</div>
                <div class="text-white/40 text-xs truncate max-w-[200px]" :title="log.user_agent">{{ log.user_agent }}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination Controls (Optional/Simplified) -->
      <div v-if="pagination && pagination.last_page > 1" class="p-4 border-t border-white/5 flex justify-between items-center text-sm">
        <button 
          @click="fetchLogs(pagination.current_page - 1)" 
          :disabled="pagination.current_page === 1"
          class="px-4 py-2 bg-white/5 rounded-lg text-white disabled:opacity-30"
        >
          Sebelumnya
        </button>
        <div class="text-white/60">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</div>
        <button 
          @click="fetchLogs(pagination.current_page + 1)" 
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 bg-white/5 rounded-lg text-white disabled:opacity-30"
        >
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const logs = ref<any[]>([])
const pagination = ref<any>(null)
const loading = ref(true)

async function fetchLogs(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/audit-logs', { params: { page } })
    logs.value = data.data?.data || data.data || []
    pagination.value = data.data?.current_page ? {
      current_page: data.data.current_page,
      last_page: data.data.last_page,
      total: data.data.total
    } : null
  } catch (e) {
    console.error('Failed to fetch audit logs', e)
  } finally {
    loading.value = false
  }
}

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '-'
}

onMounted(() => {
  fetchLogs()
})
</script>
