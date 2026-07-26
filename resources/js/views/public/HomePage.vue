<template>
  <div class="min-h-screen bg-dark">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-dark/80 backdrop-blur-md border-b border-white/5">
      <div class="container mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <img src="/logo.png" alt="Logo" class="w-10 h-10 object-contain" />
          <span class="font-bold text-white">MASIVERS</span>
          <span class="text-white/30 text-xs ml-1">COMMUNITY</span>
        </div>



        <div class="flex items-center gap-3">
          <RouterLink to="/konfirmasi-bayar" class="btn-outline text-sm py-2 px-4 hidden sm:block">Cek Status</RouterLink>
          <span v-if="isClosed" class="bg-red-500/20 text-red-400 border border-red-500/40 text-xs px-3 py-2 rounded-xl font-bold flex items-center gap-1.5">
            🔒 CLOSED
          </span>
          <RouterLink v-else to="/order-ticket" class="btn-primary text-sm py-2 px-4">Order Tiket</RouterLink>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
      <!-- Background image & overlay -->
      <div class="absolute inset-0 bg-[url('/bg-hero.jpg')] bg-cover bg-center bg-no-repeat opacity-60" />
      <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-transparent" />
      
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-dark/50 to-primary/20" />
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-3xl animate-pulse" />
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-electric/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s" />
      </div>

      <div class="relative container mx-auto px-4 py-20 text-center">

        <!-- Closed Banner Notice -->
        <div v-if="isClosed" class="max-w-2xl mx-auto mb-8 card-glass border-red-500/40 bg-red-950/40 p-6 rounded-2xl text-center space-y-2 backdrop-blur-xl shadow-2xl">
          <div class="inline-flex items-center gap-2 bg-red-500/20 border border-red-500/50 text-red-400 font-bold px-4 py-1.5 rounded-full text-xs tracking-wider">
            <span>🔒</span> PENJUALAN TIKET CLOSED
          </div>
          <h3 class="text-xl font-bold text-white">Penjualan Tiket Sedang Ditutup</h3>
          <p class="text-white/80 text-sm leading-relaxed">{{ closedMessage }}</p>
        </div>

        <!-- Title -->
        <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-4">
          <span class="gradient-text neon-glow">THE SOUNDS</span><br>
          <span class="text-white">PROJECT</span>
          <span class="text-primary"> 2026</span>
        </h1>

        <!-- Event info pills -->
        <div v-if="event" class="flex flex-wrap items-center justify-center gap-3 mb-8 text-sm">
          <div class="bg-white/5 border border-white/10 rounded-full px-4 py-1.5 text-white/70">
            📅 7, 8, 9 Agustus 2026
          </div>
          <div class="bg-white/5 border border-white/10 rounded-full px-4 py-1.5 text-white/70">
            📍 {{ event.location }}, {{ event.city }}
          </div>
        </div>

        <p class="text-white/60 text-lg max-w-2xl mx-auto mb-12">
          Dapatkan Special Harga Untuk Ticket The Sound Project<br>
          Khusus Untuk <span class="text-electric font-semibold">Masivers Community</span>
        </p>

        <!-- Countdown -->
        <div class="flex items-center justify-center gap-4 mb-12">
          <div v-for="unit in countdown" :key="unit.label" class="text-center">
            <div class="w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-1">
              {{ unit.value }}
            </div>
            <div class="text-xs text-white/40">{{ unit.label }}</div>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button v-if="isClosed" disabled class="opacity-60 cursor-not-allowed text-lg px-8 py-4 bg-white/10 text-white/40 border border-white/10 rounded-2xl font-bold">
            🔒 Penjualan Tiket Closed
          </button>
          <RouterLink v-else to="/order-ticket" class="btn-accent text-lg px-8 py-4 shadow-lg shadow-accent/20">
            🎫 Pesan Tiket Sekarang
          </RouterLink>
          <RouterLink to="/konfirmasi-bayar" class="btn-outline text-lg px-8 py-4">
            💳 Konfirmasi Pembayaran
          </RouterLink>
        </div>
      </div>
    </section>



    <!-- Footer -->
    <footer class="border-t border-white/5 py-12">
      <div class="container mx-auto px-4 text-center">
        <div v-if="event" class="flex justify-center gap-6 text-sm text-white/40 mb-6">
          <a v-if="event.contact_whatsapp" :href="`https://wa.me/${event.contact_whatsapp}`" target="_blank" class="hover:text-green-400 transition-colors">💬 WhatsApp</a>
          <a v-if="event.contact_instagram" :href="`https://instagram.com/${event.contact_instagram?.replace('@','')}`" target="_blank" class="hover:text-pink-400 transition-colors">📸 Instagram</a>
          <a v-if="event.contact_email" :href="`mailto:${event.contact_email}`" class="hover:text-blue-400 transition-colors">✉️ Email</a>
        </div>
        <p class="text-white/20 text-xs">© 2026 Masivers Community. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api'

const event = ref<any>(null)
const isClosed = ref(false)
const closedMessage = ref('')

const countdown = ref([
  { label: 'Hari', value: 0 },
  { label: 'Jam', value: 0 },
  { label: 'Menit', value: 0 },
  { label: 'Detik', value: 0 },
])



let countdownInterval: any

function updateCountdown() {
  if (!event.value?.event_date) return
  const dateStr = event.value.event_date.split('T')[0]
  const target = new Date(dateStr + 'T00:00:00+07:00').getTime()
  const now = Date.now()
  const diff = Math.max(0, target - now)
  countdown.value = [
    { label: 'Hari', value: Math.floor(diff / 86400000) },
    { label: 'Jam', value: Math.floor((diff % 86400000) / 3600000) },
    { label: 'Menit', value: Math.floor((diff % 3600000) / 60000) },
    { label: 'Detik', value: Math.floor((diff % 60000) / 1000) },
  ]
}





onMounted(async () => {
  try {
    const { data } = await api.get('/public/event')
    event.value = data.data
    isClosed.value = !!data.ticket_closed
    closedMessage.value = data.ticket_closed_message || 'Mohon maaf, penjualan tiket saat ini sedang ditutup.'
    updateCountdown()
    countdownInterval = setInterval(updateCountdown, 1000)
  } catch (e) {
    console.error('Failed to load event data', e)
  }
})

onUnmounted(() => clearInterval(countdownInterval))
</script>
