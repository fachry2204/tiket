import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // ─── Public Routes ────────────────────────────────────────────
    { path: '/', component: () => import('@/views/public/HomePage.vue') },
    { path: '/order-ticket', component: () => import('@/views/public/OrderTicketPage.vue') },
    { path: '/order-success/:orderCode', component: () => import('@/views/public/OrderSuccessPage.vue') },
    { path: '/konfirmasi-bayar', component: () => import('@/views/public/KonfirmasiBayarPage.vue') },
    { path: '/status-order/:orderCode', component: () => import('@/views/public/StatusOrderPage.vue') },
    { path: '/ticket/:ticketCode', component: () => import('@/views/public/TicketPage.vue') },
    { path: '/syarat-ketentuan', component: () => import('@/views/public/SyaratPage.vue') },
    { path: '/faq', component: () => import('@/views/public/FaqPage.vue') },

    // ─── Admin Routes ─────────────────────────────────────────────
    { path: '/admin/login', component: () => import('@/views/admin/LoginPage.vue'), meta: { public: true } },
    {
      path: '/admin',
      component: () => import('@/layouts/AdminLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/admin/dashboard' },
        { path: 'dashboard', component: () => import('@/views/admin/DashboardPage.vue') },
        { path: 'orders', component: () => import('@/views/admin/OrdersPage.vue') },
        { path: 'orders/:id', component: () => import('@/views/admin/OrderDetailPage.vue') },
        { path: 'payments', component: () => import('@/views/admin/PaymentsPage.vue') },
        { path: 'check-in', component: () => import('@/views/admin/CheckinPage.vue') },
        { path: 'ticket-products', component: () => import('@/views/admin/TicketProductsPage.vue') },
        { path: 'bank-accounts', component: () => import('@/views/admin/BankAccountsPage.vue') },
        { path: 'events', component: () => import('@/views/admin/EventPage.vue') },
        { path: 'faqs', component: () => import('@/views/admin/FaqsPage.vue') },
        { path: 'reports', component: () => import('@/views/admin/ReportsPage.vue') },
        { path: 'users', component: () => import('@/views/admin/UsersPage.vue') },
        { path: 'audit-log', component: () => import('@/views/admin/AuditLogPage.vue') },
      ]
    },
  ],
  scrollBehavior() { return { top: 0 } }
})

// Navigation guard
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth) {
    if (!auth.token) return '/admin/login'
    if (!auth.user) await auth.fetchMe()
    if (!auth.isAuthenticated) return '/admin/login'
  }
})

export default router
