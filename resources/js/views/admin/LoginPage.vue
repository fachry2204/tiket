<template>
  <div class="min-h-screen bg-dark flex items-center justify-center p-4">
    <!-- Background decorative -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-primary/10 blur-3xl" />
      <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-electric/10 blur-3xl" />
    </div>

    <div class="w-full max-w-md relative">
      <!-- Logo -->
      <div class="text-center mb-8">
        <img src="/logo.png" alt="Logo" class="w-24 h-24 object-contain mx-auto mb-4 drop-shadow-[0_0_15px_rgba(255,165,0,0.3)]" />
        <h1 class="text-2xl font-bold text-white">Masivers Ticketing</h1>
        <p class="text-white/40 text-sm mt-1">Admin Panel</p>
      </div>

      <!-- Login card -->
      <div class="card-glass p-8">
        <h2 class="text-lg font-semibold text-white mb-6">Masuk ke Dashboard</h2>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="label-field">Username</label>
            <input v-model="form.username" type="text" class="input-field" required />
          </div>

          <div>
            <label class="label-field">Password</label>
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" class="input-field pr-12" required />
              <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70 text-sm">
                {{ showPass ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.remember" type="checkbox" id="remember" class="rounded border-white/20 bg-dark-surface text-primary focus:ring-primary" />
            <label for="remember" class="text-sm text-white/60">Ingat saya selama 30 hari</label>
          </div>

          <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-red-400 text-sm">
            {{ error }}
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
            <span v-if="loading" class="animate-spin">⏳</span>
            <span>{{ loading ? 'Memproses...' : 'Masuk' }}</span>
          </button>
        </form>
      </div>

      <!-- Back to home -->
      <div class="mt-6 text-center">
        <RouterLink to="/" class="text-white/40 hover:text-white transition-colors text-sm">
          ← Kembali ke Halaman Utama
        </RouterLink>
      </div>

      <p class="text-center text-white/20 text-xs mt-6">© 2026 Masivers Community. All rights reserved.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = ref({ username: '', password: '', remember: false })
const loading = ref(false)
const error = ref('')
const showPass = ref(false)

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(form.value.username, form.value.password, form.value.remember)
    router.push('/admin/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.errors?.username?.[0] || e.response?.data?.message || 'Login gagal. Periksa kembali username dan password.'
  } finally {
    loading.value = false
  }
}
</script>
