<template>
  <div class="min-h-screen bg-dark">
    <nav class="bg-dark-card border-b border-white/5 px-4 py-4 flex items-center gap-4">
      <RouterLink to="/" class="text-white/50 hover:text-white text-sm">← Kembali</RouterLink>
      <div class="font-bold text-white">Order Tiket</div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-2xl">
      <!-- Event info banner -->
      <div v-if="event" class="card-glass p-5 mb-8 flex items-center gap-4">
        <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center text-2xl">🎵</div>
        <div>
          <div class="font-bold text-white">{{ event.name }}</div>
          <div class="text-sm text-white/50">{{ formatDate(event.event_date) }} · {{ event.location }}, {{ event.city }}</div>
        </div>
      </div>

      <form @submit.prevent="submitOrder" class="space-y-6">
        <!-- Ticket selection -->
        <div class="card-dark p-6 rounded-2xl">
          <h2 class="font-semibold text-white mb-4">Pilih Tiket</h2>
          <div v-if="loadingProducts" class="text-white/40 text-sm">Memuat tiket...</div>
          <div v-for="p in products" :key="p.id" @click="selectProduct(p)"
            :class="[
              'rounded-xl border-2 cursor-pointer transition-all', 
              selectedProduct?.id === p.id 
                ? (p.is_special ? 'border-red-500 bg-red-500/20' : 'border-primary bg-primary/10') 
                : (p.is_special ? 'border-red-500/50 hover:border-red-500/80 bg-red-500/10' : 'border-white/5 hover:border-white/20'),
              p.is_special ? 'p-6' : 'p-4'
            ]">
            <div class="flex justify-between items-start">
              <div>
                <div :class="[p.is_special ? 'text-lg font-bold text-red-400' : 'font-medium text-white']">{{ p.name }}</div>
                <div v-if="p.is_special" class="text-[10px] font-bold text-white bg-red-500 rounded px-2 py-0.5 inline-block mt-1 mb-1 tracking-wider">SPECIAL TIKET</div>
                <div class="text-sm text-white/50 mt-1">Sisa: {{ p.available_quota }} tiket</div>
              </div>
              <div class="text-right">
                <div v-if="p.promo_price" class="text-white/40 line-through text-sm">{{ formatRupiah(p.price) }}</div>
                <div :class="[p.is_special ? 'text-xl font-black text-red-400' : 'font-bold text-accent']">{{ formatRupiah(p.effective_price ?? p.price) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quantity selector -->
        <div v-if="selectedProduct" class="card-dark p-6 rounded-2xl">
          <h2 class="font-semibold text-white mb-4">Jumlah Tiket</h2>
          <div class="flex items-center gap-4">
            <button type="button" @click="qty > 1 && qty--" :disabled="qty <= 1"
              class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 font-bold text-xl flex items-center justify-center transition-colors">−</button>
            <span class="text-2xl font-bold text-white w-12 text-center">{{ qty }}</span>
            <button type="button" @click="qty < maxQty && qty++" :disabled="qty >= maxQty"
              class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 font-bold text-xl flex items-center justify-center transition-colors">+</button>
            <span class="text-sm text-white/40">Maks. {{ maxQty }} tiket per order</span>
          </div>

          <!-- Price summary -->
          <div class="mt-4 pt-4 border-t border-white/5 space-y-2">
            <div class="flex justify-between text-sm text-white/60">
              <span>{{ formatRupiah(selectedProduct.effective_price ?? selectedProduct.price) }} × {{ qty }}</span>
              <span>{{ formatRupiah((selectedProduct.effective_price ?? selectedProduct.price) * qty) }}</span>
            </div>
            <div class="flex justify-between text-sm text-white/40">
              <span>Kode unik (akan dihitung server)</span>
              <span class="text-electric">+ Rp 1–999</span>
            </div>
            <div class="flex justify-between font-bold text-white pt-2 border-t border-white/5">
              <span>Estimasi Total</span>
              <span class="text-accent">≈ {{ formatRupiah((selectedProduct.effective_price ?? selectedProduct.price) * qty) }}</span>
            </div>
          </div>
        </div>

        <!-- Customer data -->
        <div class="card-dark p-6 rounded-2xl space-y-4">
          <h2 class="font-semibold text-white">Data Pemesan</h2>
          <div>
            <label class="label-field">Nama Lengkap *</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="Nama sesuai KTP" required minlength="3" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label-field">Nomor HP *</label>
              <input v-model="form.phone" type="tel" class="input-field" placeholder="08xxxxxxxxxx" required />
            </div>
            <div>
              <label class="label-field">Email *</label>
              <input v-model="form.email" type="email" class="input-field" placeholder="email@contoh.com" required />
            </div>
          </div>
          <div>
            <label class="label-field">Alamat Lengkap *</label>
            <textarea v-model="form.address" class="input-field min-h-[80px]" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan" required minlength="10" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label-field">Provinsi *</label>
              <select v-model="form.province" @change="loadCities" class="input-field" required>
                <option value="">Pilih Provinsi</option>
                <option v-for="p in provinces" :key="p.id" :value="p.name">{{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="label-field">Kota/Kabupaten *</label>
              <select v-model="form.city" class="input-field" required :disabled="!cities.length">
                <option value="">Pilih Kota</option>
                <option v-for="c in cities" :key="c.id" :value="c.name">{{ c.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Terms -->
        <div class="card-dark p-5 rounded-2xl space-y-3">
          <label class="flex items-start gap-3 cursor-pointer">
            <input v-model="form.terms_accepted" type="checkbox" class="mt-1 rounded border-white/20 bg-dark-surface text-primary" required />
            <span class="text-sm text-white/60">Saya menyetujui <a href="/syarat-ketentuan" target="_blank" class="text-primary underline">Syarat dan Ketentuan</a> dan <a href="#" class="text-primary underline">Kebijakan Privasi</a>. Data yang saya masukkan adalah benar dan tiket tidak dapat dipindahtangankan.</span>
          </label>
        </div>

        <!-- Error -->
        <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">{{ error }}</div>

        <!-- Submit -->
        <button type="submit" :disabled="loading || !selectedProduct" class="btn-accent w-full py-4 text-lg flex items-center justify-center gap-2">
          <span v-if="loading" class="animate-spin">⏳</span>
          {{ loading ? 'Memproses...' : '🎫 Buat Order Sekarang' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '@/api'

const router = useRouter()
const event = ref<any>(null)
const products = ref<any[]>([])
const provinces = ref<any[]>([])
const cities = ref<any[]>([])
const selectedProduct = ref<any>(null)
const loadingProducts = ref(true)
const loading = ref(false)
const error = ref('')
const qty = ref(1)

const form = ref({
  name: '', phone: '', email: '', address: '',
  province: '', city: '', terms_accepted: false,
})

const maxQty = computed(() => Math.min(selectedProduct.value?.max_per_order ?? 5, selectedProduct.value?.available_quota ?? 1))

function selectProduct(p: any) {
  selectedProduct.value = p
  qty.value = 1
}

async function loadCities() {
  form.value.city = ''
  cities.value = []
  const p = provinces.value.find(p => p.name === form.value.province)
  if (!p) return
  const { data } = await api.get(`/public/provinces/${p.id}/cities`)
  cities.value = data.data
}

async function submitOrder() {
  if (!selectedProduct.value) return
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.post('/orders', {
      ...form.value,
      ticket_product_id: selectedProduct.value.id,
      quantity: qty.value,
    })
    router.push(`/order-success/${data.data.order_code}`)
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(', ')
    }
  } finally {
    loading.value = false
  }
}

function formatRupiah(n: any) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0)
}

function formatDate(d: string) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : ''
}

onMounted(async () => {
  const [evRes, prodRes, provRes] = await Promise.all([
    api.get('/public/event'),
    api.get('/public/ticket-products'),
    api.get('/public/provinces'),
  ])
  event.value = evRes.data.data
  const prodData = prodRes.data.data
  prodData.sort((a: any, b: any) => {
    if (a.is_special && !b.is_special) return -1
    if (!a.is_special && b.is_special) return 1
    return 0
  })
  products.value = prodData
  if (prodData.length > 0) {
    selectProduct(prodData[0])
  }
  provinces.value = provRes.data.data
  loadingProducts.value = false
})
</script>
