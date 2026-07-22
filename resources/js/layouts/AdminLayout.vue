<template>
  <div class="flex h-screen bg-dark overflow-hidden">
    <!-- Sidebar -->
    <aside :class="[
      'flex flex-col w-64 bg-dark-card border-r border-white/5 transition-transform duration-300',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
      'fixed inset-y-0 left-0 z-50 md:relative md:translate-x-0'
    ]">
      <!-- Logo -->
      <div class="flex items-center gap-3 p-6 border-b border-white/5">
        <img src="/logo.png" alt="Logo" class="w-10 h-10 object-contain" />
        <div>
          <div class="font-bold text-sm">Masivers</div>
          <div class="text-xs text-white/40">Admin Panel</div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <RouterLink v-for="item in navItems" :key="item.to" :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/5 transition-all duration-150"
          :class="{ 'bg-primary/20 text-primary font-medium': isActive(item.to) }">
          <span class="text-lg">{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>

      <!-- User info -->
      <div class="p-4 border-t border-white/5">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-primary/30 flex items-center justify-center text-xs font-bold">
            {{ auth.user?.name?.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium truncate">{{ auth.user?.name }}</div>
            <div class="text-xs text-white/40 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</div>
          </div>
        </div>
        <button @click="handleLogout" class="w-full text-left text-xs text-red-400 hover:text-red-300 transition-colors px-2 py-1">
          → Keluar
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Header -->
      <header class="bg-dark-card border-b border-white/5 px-6 py-4 flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-white/60 hover:text-white">
          ☰
        </button>
        <div class="flex-1">
          <h1 class="text-sm font-semibold text-white/80">{{ currentPageTitle }}</h1>
        </div>
        <div class="text-xs text-white/30">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-6">
        <RouterView />
      </main>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 md:hidden" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

const navItems = [
  { to: '/admin/dashboard', label: 'Dashboard', icon: '📊' },
  { to: '/admin/orders', label: 'Pesanan', icon: '🎫' },
  { to: '/admin/payments', label: 'Verifikasi Bayar', icon: '💳' },
  { to: '/admin/check-in', label: 'Check-in', icon: '✅' },
  { to: '/admin/ticket-products', label: 'Produk Tiket', icon: '🏷️' },
  { to: '/admin/bank-accounts', label: 'Rekening Bank', icon: '🏦' },
  { to: '/admin/events', label: 'Data Acara', icon: '🎵' },
  { to: '/admin/faqs', label: 'FAQ', icon: '❓' },
  { to: '/admin/reports', label: 'Laporan', icon: '📈' },
  { to: '/admin/users', label: 'Admin', icon: '👤' },
  { to: '/admin/settings', label: 'Pengaturan', icon: '⚙️' },
  { to: '/admin/audit-log', label: 'Audit Log', icon: '📋' },
]

const pageTitles: Record<string, string> = {
  '/admin/dashboard': 'Dashboard',
  '/admin/orders': 'Daftar Pesanan',
  '/admin/payments': 'Verifikasi Pembayaran',
  '/admin/check-in': 'Check-in Peserta',
  '/admin/ticket-products': 'Produk Tiket',
  '/admin/bank-accounts': 'Rekening Bank',
  '/admin/events': 'Data Acara',
  '/admin/faqs': 'Kelola FAQ',
  '/admin/reports': 'Laporan',
  '/admin/users': 'Kelola Admin',
  '/admin/settings': 'Pengaturan Integrasi & Notifikasi',
  '/admin/audit-log': 'Audit Log',
}

const currentPageTitle = computed(() => pageTitles[route.path] ?? 'Admin Panel')
const isActive = (path: string) => route.path === path || route.path.startsWith(path + '/')

async function handleLogout() {
  await auth.logout()
  router.push('/admin/login')
}
</script>
