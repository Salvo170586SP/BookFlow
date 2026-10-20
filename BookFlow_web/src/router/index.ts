import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/pages/LoginView.vue'
import RegisterView from '@/pages/RegisterView.vue'
import ViewLayout from '@/ViewLayout.vue'
import AdminLayout from '@/pages/admin/AdminLayout.vue'
import AdminDashboardView from '@/pages/admin/AdminDashboardView.vue'
import ForbiddenView from '@/pages/ForbiddenView.vue'
import BooksView from '@/pages/admin/books/BooksView.vue'
import AuthorsView from '@/pages/admin/authors/AuthorsView.vue'
import CategoriesView from '@/pages/admin/categories/CategoriesView.vue'
import CategoriesCreateView from '@/pages/admin/categories/CategoriesCreateView.vue'
import CategoriesEditView from '@/pages/admin/categories/CategoriesEditView.vue'
import AuthorsDetailsView from '@/pages/admin/authors/AuthorsDetailsView.vue'
import MembersView from '@/pages/admin/members/MembersView.vue'
import MemberDetailsView from '@/pages/admin/members/MemberDetailsView.vue'
import MemberEditView from '@/pages/admin/members/MemberEditView.vue'
import BooksDetailsView from '@/pages/admin/books/BooksDetailsView.vue'
import BooksLoanView from '@/pages/admin/books/BooksLoanView.vue'
import BooksAddCopiesView from '@/pages/admin/books/BooksAddCopiesView.vue'
import BooksEditCopiesView from '@/pages/admin/books/BooksEditCopiesView.vue'
import BooksCreateView from '@/pages/admin/books/BooksCreateView.vue'
import BooksEditView from '@/pages/admin/books/BooksEditView.vue'
import MemberDashboardView from '@/pages/guest/MemberDashboardView.vue'
import MemberLayout from '@/pages/guest/MemberLayout.vue'
import BooksMemberView from '@/pages/guest/books/BooksMemberView.vue'
import BooksDetailsMemberView from '@/pages/guest/books/BooksDetailsMemberView.vue'
import BooksLoanMemberView from '@/pages/guest/books/BooksLoanMemberView.vue'
import LoansBooksView from '@/pages/guest/loansBooks/LoansBooksView.vue'
import AddBookMemberView from '@/pages/guest/booksMember/AddBookMemberView.vue'
import BooksCreatedMemberView from '@/pages/guest/booksMember/BooksCreatedMemberView.vue'
  
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
      path: '/dashboard',
      component: MemberLayout,
      meta: { requiresAuth: true, roles: ['member'] },
      children: [
        { path: '', name: 'dashboard', component: MemberDashboardView },
        { path: '/books', name: 'MemberBooks', component: BooksMemberView },
        { path: '/books/:id/details', name: 'MemberDetailsBooks', component: BooksDetailsMemberView },
        { path: '/books/:id/addLoan', name: 'MemberLoanBooks', component: BooksLoanMemberView },
        { path: '/loans-books', name: 'MemberLoansBooks', component: LoansBooksView },
        { path: '/add-books', name: 'MemberAddBooks', component: AddBookMemberView },
        { path: '/books-created', name: 'MemberBooksCreated', component: BooksCreatedMemberView },
      ],
    },
    {
      path: '/admin',
      component: AdminLayout,
      redirect: '/admin/dashboard',
      meta: { requiresAuth: true, roles: ['admin'] },
      children: [
        { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboardView },
        { path: 'books', name: 'AdminBooks', component: BooksView },
        { path: 'books/create', name: 'AdminBooksCreate', component: BooksCreateView },
        { path: 'books/:id/edit', name: 'AdminBooksEdit', component: BooksEditView },
        { path: 'books/:id/details', name: 'AdminBooksDetails', component: BooksDetailsView },
        { path: 'books/:id/addLoan', name: 'AdminBooksLoan', component: BooksLoanView },
        { path: 'books/:id/addCopies', name: 'AdminBooksAddCopies', component: BooksAddCopiesView },
        { path: 'books/:id/editCopies', name: 'AdminBooksEditCopies', component: BooksEditCopiesView },
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
