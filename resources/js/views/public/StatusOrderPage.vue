<template>
  <div class="min-h-screen bg-dark p-6">
    <RouterLink to="/" class="text-white/50 hover:text-white text-sm">← Beranda</RouterLink>
    <div class="max-w-2xl mx-auto mt-6">
      <h1 class="text-2xl font-bold text-white mb-4">Status Order</h1>
      <div v-if="loading" class="text-white/40">Memuat...</div>
      <div v-else-if="order" class="card-dark p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
          <div class="font-mono text-electric">{{ order.order_code }}</div>
          <span :class="statusBadge(order.order_status)" class="badge">{{ statusLabel(order.order_status) }}</span>
        </div>
        <div class="space-y-2 text-sm text-white/60">
          <div class="flex justify-between"><span>Nama</span><span class="text-white">{{ order.customer?.name }}</span></div>
          <div class="flex justify-between"><span>Email</span><span class="text-white">{{ order.customer?.email }}</span></div>
          <div class="flex justify-between"><span>Total</span><span class="text-accent font-bold">{{ formatRupiah(order.grand_total) }}</span></div>
          <div class="flex justify-between"><span>Batas Bayar</span><span>{{ order.expires_at ? new Date(order.expires_at).toLocaleString('id-ID') : '-' }}</span></div>
        </div>
        <div v-if="order.order_status === 'paid'" class="mt-4 bg-white/5 border border-white/10 rounded-xl p-6 text-center">
          <div class="text-accent font-bold text-lg mb-2">🎟 E-Ticket / Bukti Penukaran</div>
          <p class="text-white/50 text-sm mb-6">Tunjukkan QR Code ini kepada PIC MASIVERS di lokasi untuk penukaran tiket fisik Atau Cek Email Atau Whatsapp Anda Untuk Melihat Ticket Anda.</p>
          
          <div class="bg-white p-4 rounded-xl inline-block mb-4 shadow-[0_0_20px_rgba(0,194,255,0.2)]">
            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${order.order_code}`" alt="QR Code" class="w-48 h-48 object-contain" />
          </div>
          
          <div class="font-mono text-electric text-2xl font-bold tracking-widest">{{ order.order_code }}</div>
          <div class="text-white/40 text-xs mt-2">No. Invoice: {{ order.invoice_number }}</div>
        </div>
        <div v-if="order.bank_account && order.order_status === 'pending_payment'" class="mt-4 bg-primary/10 border border-primary/20 rounded-xl p-4">
          <div class="text-xs text-white/40 mb-2">Transfer ke:</div>
          <div class="font-bold text-white">{{ order.bank_account.bank_name }}</div>
          <div class="font-mono text-electric text-xl mt-1">{{ order.bank_account.account_number }}</div>
          <div class="text-white/60 text-sm">a.n. {{ order.bank_account.account_holder_name }}</div>
          <div class="text-accent font-bold text-xl mt-2">{{ formatRupiah(order.grand_total) }}</div>
          <RouterLink to="/konfirmasi-bayar" class="btn-primary text-sm inline-block mt-3 px-4 py-2">Upload Bukti Bayar</RouterLink>
        </div>
      </div>
      <div v-else class="text-white/40">Order tidak ditemukan</div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/api'
const route = useRoute()
const order = ref<any>(null)
const loading = ref(true)
const formatRupiah = (n: any) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
const statusBadge = (s: string) => ({ pending_payment: 'badge-pending', waiting_verification: 'badge-waiting', paid: 'badge-paid', payment_rejected: 'badge-rejected', expired: 'badge-expired' })[s] ?? 'badge'
const statusLabel = (s: string) => ({ pending_payment: 'Menunggu Bayar', waiting_verification: 'Menunggu Verifikasi', paid: 'Lunas', payment_rejected: 'Ditolak', expired: 'Kedaluwarsa' })[s] ?? s
onMounted(async () => { try { const { data } = await api.get(`/orders/${route.params.orderCode}`); order.value = data.data } finally { loading.value = false } })
</script>
