<template>
  <div class="p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-white">Pengaturan Acara</h1>
      <button @click="saveEvent" :disabled="processing || loading" class="btn-primary flex items-center gap-2">
        <span>{{ processing ? 'Menyimpan...' : '💾 Simpan Perubahan' }}</span>
      </button>
    </div>
    
    <div v-if="loading" class="card-dark p-8 text-center text-white/40 rounded-xl">
      Memuat data acara...
    </div>
    
    <div v-else-if="event" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Info Utama -->
      <div class="card-dark rounded-xl p-6 space-y-4">
        <h2 class="font-semibold text-white mb-2">Informasi Utama</h2>
        
        <div>
          <label class="label-field">Nama Acara *</label>
          <input v-model="form.name" type="text" class="input-field" required />
        </div>
        
        <div>
          <label class="label-field">Deskripsi</label>
          <textarea v-model="form.description" class="input-field min-h-[100px]"></textarea>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label-field">Tanggal Acara *</label>
            <input v-model="form.event_date" type="date" class="input-field" required />
          </div>
          <div>
            <label class="label-field">Kota *</label>
            <input v-model="form.city" type="text" class="input-field" required />
          </div>
        </div>
        
        <div>
          <label class="label-field">Lokasi Spesifik *</label>
          <input v-model="form.location" type="text" class="input-field" required />
        </div>
        
        <div class="flex items-center gap-3 pt-4 border-t border-white/10">
          <input v-model="form.is_published" type="checkbox" class="rounded border-white/20 bg-dark-surface text-primary" />
          <label class="text-white font-medium">Acara Dipublikasikan (Aktif)</label>
        </div>
      </div>
      
      <!-- Kontak & Media -->
      <div class="space-y-6">
        <div class="card-dark rounded-xl p-6 space-y-4">
          <h2 class="font-semibold text-white mb-2">Kontak Bantuan</h2>
          
          <div>
            <label class="label-field">Nomor WhatsApp (Contoh: 62812...)</label>
            <input v-model="form.contact_whatsapp" type="text" class="input-field" />
          </div>
          
          <div>
            <label class="label-field">Username Instagram (Tanpa @)</label>
            <input v-model="form.contact_instagram" type="text" class="input-field" />
          </div>
          
          <div>
            <label class="label-field">Email Dukungan</label>
            <input v-model="form.contact_email" type="email" class="input-field" />
          </div>
        </div>
        
        <div class="card-dark rounded-xl p-6 space-y-4">
          <h2 class="font-semibold text-white mb-2">Banner Acara</h2>
          <div v-if="form.banner_image_path" class="mb-4">
            <img :src="form.banner_image_path" alt="Banner" class="w-full rounded-lg object-cover h-40 border border-white/10" />
          </div>
          <div class="p-4 border-2 border-dashed border-white/20 rounded-lg text-center text-white/50 text-sm">
            <p class="mb-2">Upload banner (Dalam pengembangan)</p>
            <input type="text" v-model="form.banner_image_path" class="input-field text-xs mt-2" placeholder="URL Banner (Opsional)" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const event = ref<any>(null)
const loading = ref(true)
const processing = ref(false)

const form = ref({
  name: '',
  description: '',
  event_date: '',
  location: '',
  city: '',
  banner_image_path: '',
  contact_email: '',
  contact_whatsapp: '',
  contact_instagram: '',
  is_published: false
})

async function fetchEvent() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/events')
    if (data.data && data.data.length > 0) {
      event.value = data.data[0]
      form.value = {
        name: event.value.name || '',
        description: event.value.description || '',
        event_date: event.value.event_date || '',
        location: event.value.location || '',
        city: event.value.city || '',
        banner_image_path: event.value.banner_image_path || '',
        contact_email: event.value.contact_email || '',
        contact_whatsapp: event.value.contact_whatsapp || '',
        contact_instagram: event.value.contact_instagram || '',
        is_published: !!event.value.is_published
      }
    }
  } catch (e) {
    console.error('Failed to fetch event', e)
  } finally {
    loading.value = false
  }
}

async function saveEvent() {
  if (!event.value) return
  processing.value = true
  try {
    await api.patch(`/admin/events/${event.value.id}`, form.value)
    alert('Pengaturan acara berhasil disimpan!')
    fetchEvent()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menyimpan data')
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  fetchEvent()
})
</script>
