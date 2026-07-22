<template>
  <div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Pengaturan Integrasi & Notifikasi</h1>
        <p class="text-sm text-white/50 mt-1">Kelola konfigurasi pengiriman Email (SMTP) dan WhatsApp Gateway (MPWA)</p>
      </div>
      <button @click="saveSettings" :disabled="saving" class="btn-primary flex items-center gap-2 px-6 py-2.5">
        <span>💾</span>
        <span>{{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
      </button>
    </div>

    <div v-if="successMsg" class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-2xl flex items-center justify-between">
      <span>✅ {{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-green-400/60 hover:text-green-400">✕</button>
    </div>

    <div v-if="errorMsg" class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-2xl flex items-center justify-between">
      <span>⚠️ {{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-red-400/60 hover:text-red-400">✕</button>
    </div>

    <!-- Domain Website Settings Card -->
    <div class="card-glass p-6">
      <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
        <div>
          <div class="font-semibold text-white">🌐 URL Domain Utama Website</div>
          <div class="text-xs text-white/40">Domain ini digunakan untuk semua link tombol konfirmasi bayar & e-tiket di Email & WhatsApp</div>
        </div>
      </div>
      <div>
        <label class="label-field">URL Domain (Contoh: https://tiket.masivers.id atau https://masivers.id)</label>
        <input v-model="form.app_frontend_url" type="text" class="input-field" placeholder="https://tiket.masivers.id (Kosongkan jika ingin otomatis mendeteksi domain saat ini)" />
        <p class="text-xs text-white/30 mt-1.5">Jika diisi, semua link notifikasi Email & WA akan otomatis mengarah ke domain ini. Jika dikosongkan, sistem otomatis mendeteksi nama domain aktif saat ini.</p>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-white/10 gap-4">
      <button
        @click="activeTab = 'smtp'"
        :class="['pb-3 px-2 font-medium text-sm transition-colors relative', activeTab === 'smtp' ? 'text-primary' : 'text-white/60 hover:text-white']">
        ✉️ Notifikasi Email (SMTP)
        <span v-if="activeTab === 'smtp'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full" />
      </button>
      <button
        @click="activeTab = 'wa'"
        :class="['pb-3 px-2 font-medium text-sm transition-colors relative', activeTab === 'wa' ? 'text-primary' : 'text-white/60 hover:text-white']">
        💬 WhatsApp Gateway (Fonnte.com)
        <span v-if="activeTab === 'wa'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full" />
      </button>
    </div>

    <!-- Tab Content: SMTP -->
    <div v-if="activeTab === 'smtp'" class="space-y-6">
      <div class="card-glass p-6 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
          <div>
            <div class="font-semibold text-white">Status Notifikasi Email</div>
            <div class="text-xs text-white/40">Kirim email otomatis saat pemesanan baru, verifikasi bayar, dan terbit tiket</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.mail_enabled" true-value="1" false-value="0" class="sr-only peer" />
            <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
          </label>
        </div>

        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-xs text-blue-300 space-y-1.5">
          <div class="font-bold flex items-center gap-1.5 text-sm text-blue-200">
            <span>💡</span> Panduan Pengaturan Gmail SMTP:
          </div>
          <p>• <strong>SMTP Host</strong>: <code class="bg-black/30 px-1.5 py-0.5 rounded text-white">smtp.gmail.com</code> | <strong>Port</strong>: <code class="bg-black/30 px-1.5 py-0.5 rounded text-white">587</code> | <strong>Enkripsi</strong>: <code class="bg-black/30 px-1.5 py-0.5 rounded text-white">TLS</code></p>
          <p>• <strong>SMTP Username</strong>: Alamat Gmail Anda (contoh: <code class="bg-black/30 px-1.5 py-0.5 rounded text-white">masivers@gmail.com</code>)</p>
          <p>• <strong>SMTP Password</strong>: Akses langsung halaman Sandi Aplikasi via link: <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline text-blue-200 font-bold">https://myaccount.google.com/apppasswords</a> (Pastikan Verifikasi 2-Langkah / 2-Step Verification pada akun Google Anda sudah Aktif).</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label-field">SMTP Host</label>
            <input v-model="form.mail_host" type="text" class="input-field" placeholder="smtp.gmail.com atau mail.domain.com" />
          </div>

          <div>
            <label class="label-field">SMTP Port</label>
            <input v-model="form.mail_port" type="number" class="input-field" placeholder="587 / 465" />
          </div>

          <div>
            <label class="label-field">SMTP Username / Email Akun</label>
            <input v-model="form.mail_username" type="text" class="input-field" placeholder="email@domain.com" />
          </div>

          <div>
            <label class="label-field">SMTP Password / App Password</label>
            <div class="relative">
              <input v-model="form.mail_password" :type="showSmtpPass ? 'text' : 'password'" class="input-field pr-12" placeholder="••••••••" />
              <button type="button" @click="showSmtpPass = !showSmtpPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white text-sm">
                {{ showSmtpPass ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div>
            <label class="label-field">Tipe Enkripsi</label>
            <select v-model="form.mail_encryption" class="input-field bg-dark-card">
              <option value="tls">TLS (Port 587 - Recommended)</option>
              <option value="ssl">SSL (Port 465)</option>
              <option value="none">None / Tanpa Enkripsi</option>
            </select>
          </div>

          <div>
            <label class="label-field">Alamat Email Pengirim (From Address)</label>
            <input v-model="form.mail_from_address" type="email" class="input-field" placeholder="noreply@masivers.id" />
          </div>

          <div class="md:col-span-2">
            <label class="label-field">Nama Display Pengirim (From Name)</label>
            <input v-model="form.mail_from_name" type="text" class="input-field" placeholder="Masivers Ticketing" />
          </div>
        </div>

        <div class="pt-4 border-t border-white/10 flex items-center justify-between">
          <span class="text-xs text-white/40">Pastikan kredensial SMTP sudah benar sebelum melakukan tes pengiriman.</span>
          <button type="button" @click="openTestEmailModal" class="btn-outline text-sm px-4 py-2 flex items-center gap-2">
            <span>🧪</span> Uji Coba Pengiriman Email
          </button>
        </div>
      </div>
    </div>

    <!-- Tab Content: WA Gateway (Fonnte.com) -->
    <div v-if="activeTab === 'wa'" class="space-y-6">
      <div class="card-glass p-6 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
          <div>
            <div class="font-semibold text-white">Status Notifikasi WhatsApp</div>
            <div class="text-xs text-white/40">Kirim pesan WhatsApp otomatis via Fonnte.com untuk setiap perubahan status pesanan</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.wa_gateway_enabled" true-value="1" false-value="0" class="sr-only peer" />
            <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
          </label>
        </div>

        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-xs text-emerald-300 space-y-1.5">
          <div class="font-bold flex items-center gap-1.5 text-sm text-emerald-200">
            <span>💡</span> Panduan Pengaturan Fonnte.com WhatsApp Gateway:
          </div>
          <p>• <strong>Dapatkan API Token</strong>: Daftar & Login di Dashboard Fonnte (<a href="https://md.fonnte.com" target="_blank" class="underline text-emerald-200 font-bold">https://md.fonnte.com</a>) → Menu Device → Copy <strong>Token</strong> perangkat Anda.</p>
          <p>• <strong>URL Endpoint API</strong>: Biarkan default <code class="bg-black/30 px-1.5 py-0.5 rounded text-white">https://api.fonnte.com/send</code></p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="label-field">URL Endpoint API Fonnte</label>
            <input v-model="form.wa_gateway_url" type="text" class="input-field" placeholder="https://api.fonnte.com/send" />
            <p class="text-xs text-white/30 mt-1">Endpoint HTTP POST pengiriman pesan dari Fonnte.com</p>
          </div>

          <div>
            <label class="label-field">API Token Fonnte *</label>
            <div class="relative">
              <input v-model="form.wa_gateway_api_key" :type="showWaKey ? 'text' : 'password'" class="input-field pr-12" placeholder="Masukkan Token Fonnte Anda" />
              <button type="button" @click="showWaKey = !showWaKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white text-sm">
                {{ showWaKey ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-white/10 flex items-center justify-between">
          <span class="text-xs text-white/40">Pastikan device Fonnte di dashboard Fonnte.com dalam kondisi Connected.</span>
          <button type="button" @click="openTestWaModal" class="btn-outline text-sm px-4 py-2 flex items-center gap-2">
            <span>🧪</span> Uji Coba Pengiriman WhatsApp
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Test Email -->
    <div v-if="showTestEmailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="card-dark w-full max-w-md rounded-2xl p-6 relative">
        <button @click="showTestEmailModal = false" class="absolute top-4 right-4 text-white/40 hover:text-white">✕</button>
        <h3 class="text-lg font-bold text-white mb-2">Uji Coba Pengiriman Email</h3>
        <p class="text-xs text-white/50 mb-4">Sistem akan mencoba mengirim pesan uji coba ke alamat email tujuan berikut.</p>
        
        <form @submit.prevent="sendTestEmail" class="space-y-4">
          <div>
            <label class="label-field">Email Tujuan *</label>
            <input v-model="testEmailAddress" type="email" class="input-field" placeholder="emailanda@domain.com" required />
          </div>
          
          <div v-if="testEmailError" class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3 rounded-xl">
            {{ testEmailError }}
          </div>

          <div v-if="testEmailSuccess" class="bg-green-500/10 border border-green-500/20 text-green-400 text-xs p-3 rounded-xl">
            {{ testEmailSuccess }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showTestEmailModal = false" class="px-4 py-2 text-sm text-white/60 hover:text-white">Tutup</button>
            <button type="submit" :disabled="testingEmail" class="btn-primary py-2 px-4 text-sm">
              {{ testingEmail ? 'Mengirim...' : 'Kirim Uji Coba' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Test WhatsApp -->
    <div v-if="showTestWaModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="card-dark w-full max-w-md rounded-2xl p-6 relative">
        <button @click="showTestWaModal = false" class="absolute top-4 right-4 text-white/40 hover:text-white">✕</button>
        <h3 class="text-lg font-bold text-white mb-2">Uji Coba Pengiriman WhatsApp</h3>
        <p class="text-xs text-white/50 mb-4">Sistem akan menguji API Fonnte.com dengan mengirim pesan ke nomor WhatsApp tujuan.</p>
        
        <form @submit.prevent="sendTestWa" class="space-y-4">
          <div>
            <label class="label-field">Nomor WhatsApp Tujuan *</label>
            <input v-model="testWaPhone" type="text" class="input-field" placeholder="08xxxxxxxxxx" required />
          </div>
          
          <div v-if="testWaError" class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3 rounded-xl">
            {{ testWaError }}
          </div>

          <div v-if="testWaSuccess" class="bg-green-500/10 border border-green-500/20 text-green-400 text-xs p-3 rounded-xl">
            {{ testWaSuccess }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showTestWaModal = false" class="px-4 py-2 text-sm text-white/60 hover:text-white">Tutup</button>
            <button type="submit" :disabled="testingWa" class="btn-primary py-2 px-4 text-sm">
              {{ testingWa ? 'Mengirim...' : 'Kirim Uji Coba' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const activeTab = ref<'smtp' | 'wa'>('smtp')
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const showSmtpPass = ref(false)
const showWaKey = ref(false)

const form = ref<Record<string, any>>({
  app_frontend_url: '',
  mail_enabled: '0',
  mail_host: '',
  mail_port: 587,
  mail_username: '',
  mail_password: '',
  mail_encryption: 'tls',
  mail_from_address: '',
  mail_from_name: '',
  wa_gateway_enabled: '0',
  wa_gateway_url: '',
  wa_gateway_api_key: '',
  wa_gateway_sender: '',
})

// Test email modal state
const showTestEmailModal = ref(false)
const testEmailAddress = ref('')
const testingEmail = ref(false)
const testEmailSuccess = ref('')
const testEmailError = ref('')

// Test WA modal state
const showTestWaModal = ref(false)
const testWaPhone = ref('')
const testingWa = ref(false)
const testWaSuccess = ref('')
const testWaError = ref('')

async function fetchSettings() {
  try {
    const { data } = await api.get('/admin/settings')
    Object.keys(form.value).forEach((key) => {
      if (data.data[key] !== undefined) {
        form.value[key] = data.data[key]
      }
    })
  } catch (e: any) {
    errorMsg.value = 'Gagal memuat pengaturan.'
  }
}

async function saveSettings() {
  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    await api.patch('/admin/settings', { settings: form.value })
    successMsg.value = 'Pengaturan integrasi berhasil disimpan.'
  } catch (e: any) {
    errorMsg.value = e.response?.data?.message || 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

function openTestEmailModal() {
  testEmailSuccess.value = ''
  testEmailError.value = ''
  showTestEmailModal.value = true
}

async function sendTestEmail() {
  testingEmail.value = true
  testEmailSuccess.value = ''
  testEmailError.value = ''
  try {
    // Auto-save form settings first
    await api.patch('/admin/settings', { settings: form.value })
    const { data } = await api.post('/admin/settings/test-smtp', { email: testEmailAddress.value })
    testEmailSuccess.value = data.message
  } catch (e: any) {
    testEmailError.value = e.response?.data?.message || 'Uji coba gagal.'
  } finally {
    testingEmail.value = false
  }
}

function openTestWaModal() {
  testWaSuccess.value = ''
  testWaError.value = ''
  showTestWaModal.value = true
}

async function sendTestWa() {
  testingWa.value = true
  testWaSuccess.value = ''
  testWaError.value = ''
  try {
    // Auto-save form settings first
    await api.patch('/admin/settings', { settings: form.value })
    const { data } = await api.post('/admin/settings/test-wa', { phone: testWaPhone.value })
    testWaSuccess.value = data.message
  } catch (e: any) {
    testWaError.value = e.response?.data?.message || 'Uji coba gagal.'
  } finally {
    testingWa.value = false
  }
}

onMounted(fetchSettings)
</script>
