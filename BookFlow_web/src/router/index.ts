import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/pages/LoginView.vue'
import DashboardView from '@/pages/DashboardView.vue'
import RegisterView from '@/pages/RegisterView.vue'
import ViewLayout from '@/ViewLayout.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/', name: 'home', component: ViewLayout,
    },
    {
      path: '/login', name: 'login', component: LoginView, meta: { guest: true }
    },
    {
      path: '/register', name: 'register', component: RegisterView, meta: { guest: true }
    },
    {
      path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true }
    },
  ],
})


router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Al primo caricamento chiediamo al server se esiste già una sessione
  await auth.init()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
