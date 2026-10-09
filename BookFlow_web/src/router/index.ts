import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/pages/LoginView.vue'
import DashboardView from '@/pages/guest/DashboardView.vue'
import RegisterView from '@/pages/RegisterView.vue'
import ViewLayout from '@/ViewLayout.vue'
import AdminLayout from '@/pages/admin/AdminLayout.vue'
import AdminDashboardView from '@/pages/admin/AdminDashboardView.vue'
import ForbiddenView from '@/pages/ForbiddenView.vue'
import BooksView from '@/pages/admin/BooksView.vue'
import AuthorsView from '@/pages/admin/AuthorsView.vue'
import CategoriesView from '@/pages/admin/CategoriesView.vue'
import CategoriesCreateView from '@/pages/admin/CategoriesCreateView.vue'
import CategoriesEditView from '@/pages/admin/CategoriesEditView.vue'
import AuthorsDetailsView from '@/pages/admin/AuthorsDetailsView.vue'
import MembersView from '@/pages/admin/MembersView.vue'
import MemberDetailsView from '@/pages/admin/MemberDetailsView.vue'
import MemberEditView from '@/pages/admin/MemberEditView.vue'
import BooksLoanView from '@/pages/admin/BooksLoanView.vue'
import BooksAddCopiesView from '@/pages/admin/BooksAddCopiesView.vue'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guest?: boolean
    roles?: string[]
  }
}

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
      path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true, roles: ['member'] }
    },
    {
      path: '/admin',
      component: AdminLayout,
      redirect: '/admin/dashboard',
      meta: { requiresAuth: true, roles: ['admin'] },
      children: [
        { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboardView },
        { path: 'books', name: 'AdminBooks', component: BooksView },
        { path: 'books/:id/addLoan', name: 'AdminBooksLoan', component: BooksLoanView },
        { path: 'books/:id/addCopies', name: 'AdminBooksAddCopies', component: BooksAddCopiesView },
        { path: 'authors', name: 'AdminAuthors', component: AuthorsView },
        { path: 'authors/:id/details', name: 'AdminAuthorsDetails', component: AuthorsDetailsView },
        { path: 'categories', name: 'AdminCategories', component: CategoriesView },
        { path: 'categories/create', name: 'AdminCategoriesCreate', component: CategoriesCreateView },
        { path: 'categories/edit/:id', name: 'AdminCategoriesEdit', component: CategoriesEditView },
        { path: 'members', name: 'AdminMembers', component: MembersView },
        { path: 'members/:id/details', name: 'AdminMembersDetails', component: MemberDetailsView },
        { path: 'members/:id/edit', name: 'AdminMembersEdit', component: MemberEditView },
      ],
    },
    {
      path: '/forbidden', name: 'forbidden', component: ForbiddenView, meta: { requiresAuth: true },
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
    return auth.hasRole('admin') ? '/admin/dashboard' : { name: 'dashboard' }
  }

  // La pagina richiede dei ruoli che l'utente non ha: accesso negato
  if (to.meta.roles && !auth.hasAnyRole(...to.meta.roles)) {
    return { name: 'forbidden' }
  }
})

export default router
