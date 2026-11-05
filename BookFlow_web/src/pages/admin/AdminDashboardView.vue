<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { fetchBooks } from '../../api/books'
import { useAuthStore } from '../../stores/auth'
import { RouterLink } from 'vue-router'
import { fetchMembers } from '@/api/members'

const auth = useAuthStore()
const totalBooks = ref<number | null>(null)
const booksError = ref<string | null>(null)

const totalMembers = ref<number | null>(null)
const membersError = ref<string | null>(null)

onMounted(async () => {
  try {
    const books = await fetchBooks()
    totalBooks.value = books.meta.total
  } catch {
    booksError.value = 'Impossibile caricare il totale dei libri.'
  }

  try {
    const members = await fetchMembers()
    totalMembers.value = members.meta.total
  } catch {
    membersError.value = 'Impossibile caricare il totale dei membri.'
  }
})
</script>

<template>
  <main class="min-h-full bg-[#f5f1e8] px-4 py-10">
    <div class="mx-auto max-w-7xl">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">
        Benvenuto Admin,
        <span v-if="auth.user">{{ auth.user.name }}</span>
      </h1>
      <p class="mt-1 text-slate-600">Ecco il riepilogo del tuo account.</p>

      <div v-if="auth.user" class="mt-8 grid gap-6 sm:grid-cols-2">
        <div class="space-y-3 border border-[#242820]/20 bg-[#fffdf8] p-6">
          <div>
            <p class="text-sm font-medium text-slate-500">Nome</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user.name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Email</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user.email }}</p>
          </div>
        </div>

        <div class="border border-[#242820]/20 bg-[#fffdf8] p-6">
          <div>
            <p class="text-lg font-medium text-slate-500">Catalogo</p>
            <p class="my-1 text-lg font-semibold text-slate-900 mb-5">
              Tot. libri in catalogo:
              <span v-if="totalBooks !== null">{{ totalBooks }}</span>
              <span v-else-if="booksError" class="text-sm font-medium text-red-600">
                {{ booksError }}
              </span>
              <span v-else aria-label="Caricamento">…</span>
            </p>
            <RouterLink to="/admin/books" class="border border-[#242820]/25 px-3 py-1 font-semibold transition hover:bg-[#ede7da]"
              >Accedi al catalogo</RouterLink
            >
          </div>
        </div>

        <div class="border border-[#242820]/20 bg-[#fffdf8] p-6">
          <div>
            <p class="text-lg font-medium text-slate-500">Membri registrati</p>
            <p class="my-1 text-lg font-semibold text-slate-900 mb-5">
              Tot. membri registrati:
              <span v-if="totalMembers !== null">{{ totalMembers }}</span>
              <span v-else-if="membersError" class="text-sm font-medium text-red-600">
                {{ membersError }}
              </span>
              <span v-else aria-label="Caricamento">…</span>
            </p>
            <RouterLink to="/admin/members" class="border border-[#242820]/25 px-3 py-1 font-semibold transition hover:bg-[#ede7da]"
              >Accedi ai membri</RouterLink
            >
          </div>
        </div>
      </div>
    </div>
  </main>
</template>