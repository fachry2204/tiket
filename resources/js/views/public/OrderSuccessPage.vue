<template>
  <div class="min-h-screen bg-dark">
    <nav class="bg-dark-card border-b border-white/5 px-4 py-4 flex items-center gap-4">
      <RouterLink to="/" class="text-white/50 hover:text-white text-sm">← Beranda</RouterLink>
      <div class="font-bold text-white">Detail Order</div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-2xl">
      <div v-if="loading" class="text-center py-20 text-white/40">Memuat...</div>

      <div v-else-if="order">
        <!-- Success banner -->
        <div class="card-glass p-6 mb-6 text-center border-green-500/20">
          <div class="text-4xl mb-3">🎉</div>
          <h1 class="text-xl font-bold text-white">Order Berhasil Dibuat!</h1>
          <p class="text-white/50 text-sm mt-2">Segera lakukan pembayaran sebelum batas waktu habis</p>
          <div class="inline-block mt-3 font-mono text-electric bg-electric/10 px-4 py-2 rounded-lg text-sm">
            {{ order.order_code }}
          </div>
        </div>

        <!-- Order detail -->
        <div class="card-dark p-6 rounded-2xl mb-6">
          <h2 class="font-semibold text-white mb-4">Detail Pesanan</h2>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between text-white/60">
              <span>Nama</span><span class="text-white">{{ order.customer?.name }}</span>
            </div>
            <div class="flex justify-between text-white/60">
              <span>Email</span><span class="text-white">{{ order.customer?.email }}</span>
            </div>
            <div class="flex justify-between text-white/60">
              <span>No. HP</span><span class="text-white">{{ order.customer?.phone }}</span>
            </div>
            <div class="border-t border-white/5 pt-3" v-for="item in order.items" :key="item.id">
              <div class="flex justify-between text-white/60">
                <span>{{ item.ticket_name }}</span>
                <span class="text-white">{{ item.quantity }}× {{ formatRupiah(item.price_snapshot) }}</span>
              </div>
            </div>
            <div class="border-t border-white/5 pt-3 flex justify-between font-bold text-white">
              <span>Total Transfer</span>
              <span class="text-accent text-lg">{{ formatRupiah(order.grand_total) }}</span>
            </div>
            <div class="text-white/40 text-xs">* Termasuk kode unik Rp {{ order.unique_code }}</div>
          </div>
        </div>

        <!-- Payment instruction -->
        <div class="card-dark p-6 rounded-2xl mb-6" v-if="order.bank_account">
          <h2 class="font-semibold text-white mb-4">Instruksi Pembayaran</h2>
          <div class="bg-primary/10 border border-primary/20 rounded-xl p-4 mb-4">
            <div class="text-xs text-white/50 mb-1">Bank Tujuan</div>
            <div class="font-bold text-white text-lg">{{ order.bank_account.bank_name }}</div>
            <div class="flex items-center gap-3 mt-2">
              <span class="font-mono text-electric text-xl font-bold">{{ order.bank_account.account_number }}</span>
              <button @click="copyText(order.bank_account.account_number)" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded transition-colors">Salin</button>
            </div>
            <div class="text-white/60 text-sm mt-1">a.n. {{ order.bank_account.account_holder_name }}</div>
          </div>

          <div class="bg-accent/10 border border-accent/20 rounded-xl p-4">
            <div class="text-xs text-white/50 mb-1">Nominal Transfer (Persis)</div>
            <div class="flex items-center gap-3">
              <span class="font-bold text-accent text-2xl">{{ formatRupiah(order.grand_total) }}</span>
              <button @click="copyText(order.grand_total)" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded transition-colors">Salin</button>
            </div>
          </div>

          <div class="mt-4 text-sm text-white/50">
            <span class="text-red-400 font-semibold">Batas Waktu: </span>
            {{ formatDateTime(order.expires_at) }}
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <RouterLink :to="`/konfirmasi-bayar?search=${order.customer?.phone}`" class="btn-outline text-center py-3 text-sm">💳 Konfirmasi Bayar</RouterLink>
          <RouterLink :to="`/status-order/${order.order_code}`" class="btn-primary text-center py-3 text-sm">📊 Cek Status</RouterLink>
        </div>
      </div>

      <div v-else class="text-center py-20 text-white/40">Order tidak ditemukan</div>
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

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDateTime(d: string) {
  return d ? new Date(d).toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' }) : '-'
}

function copyText(text: any) {
  navigator.clipboard.writeText(String(text))
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/orders/${route.params.orderCode}`)
    order.value = data.data
  } finally {
    loading.value = false
  }
})
</script>
