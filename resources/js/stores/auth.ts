import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

interface AdminUser {
  id: number
  name: string
  email: string
  role: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AdminUser | null>(null)
  const token = ref<string | null>(localStorage.getItem('admin_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  async function login(email: string, password: string, remember = false) {
    loading.value = true
    try {
      const { data } = await api.post('/admin/auth/login', { email, password, remember })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('admin_token', data.token)
      return true
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/admin/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('admin_token')
    }
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const { data } = await api.get('/admin/auth/me')
      user.value = data.user
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('admin_token')
    }
  }

  return { user, token, loading, isAuthenticated, isSuperAdmin, login, logout, fetchMe }
})
