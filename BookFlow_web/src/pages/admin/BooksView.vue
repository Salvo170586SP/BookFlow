<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchBooks } from './../../api/books'
import type { Book, PaginationMeta } from './../../types/book'
import type { Category } from './../../types/category'
import { fetchCategoriesOptions } from './../../api/categories'

const route = useRoute()
const router = useRouter()

const books = ref<Book[]>([])
const categories = ref<Category[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

async function loadCategories() {
  try {
    categories.value = await fetchCategoriesOptions()
  } catch {
    console.error('Impossibile caricare le categorie')
  }
}

// La pagina corrente sta nell'URL (?page=2): il refresh e il tasto "indietro" funzionano
const page = computed(() => Math.max(Number(route.query.page) || 1, 1))
const categoryId = computed(() => {
  const value = route.query.category_id
  if (typeof value !== 'string' || value === '') return undefined

  const id = Number(value)
  return Number.isInteger(id) && id > 0 ? id : undefined
})

// Contatore delle richieste: serve a ignorare le risposte arrivate in ritardo
let lastRequest = 0

async function load() {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null

  try {
    const result = await fetchBooks(page.value, categoryId.value)

    // Se nel frattempo è partita una richiesta più recente, questa risposta è vecchia
    if (requestId !== lastRequest) return

    // Pagina oltre l'ultima (es. ?page=999): portiamo l'utente all'ultima disponibile
    if (result.meta.last_page > 0 && page.value > result.meta.last_page) {
      router.replace({ query: { ...route.query, page: result.meta.last_page } })
      return
    }

    books.value = result.data
    meta.value = result.meta
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare i libri. Riprova.'
    }
  } finally {
    if (requestId === lastRequest) {
      loading.value = false
    }
  }
}

function goToPage(n: number) {
  router.push({ query: { ...route.query, page: n } })
}

function filterByCategory(event: Event) {
  const target = event.target
  if (!(target instanceof HTMLSelectElement)) return

  router.push({
    query: {
      ...route.query,
      page: undefined,
      category_id: target.value || undefined,
    },
  })
}

// L'URL conserva i filtri e la pagina, quindi funzionano refresh e tasto "indietro"
watch([page, categoryId], load, { immediate: true })
loadCategories()
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 pb-7">Catalogo</h1>

      <div v-if="loading && books.length === 0" class="flex flex-col justify-center items-center">
        <div
          class="size-8 animate-spin rounded-full border-4 border-solid border-gray-300 border-t-black"
        ></div>
        <span class="text-black">Caricamento...</span>
      </div>

      <section v-else>
        <select
          id="categories"
          name="category"
          class="w-100 border bg-white"
          :value="categoryId ?? ''"
          @change="filterByCategory"
        >
          <option value="">-Seleziona-</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>

        <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ error }}
        </p>

        <div
          class="mt-6 overflow-x-auto border border-slate-400 bg-white transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Titolo</th>
                <th class="px-4 py-3">Autori</th>
                <th class="px-4 py-3">Categoria</th>
                <th class="px-4 py-3">Editore</th>
                <th class="px-4 py-3">Anno</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="book in books" :key="book.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ book.title }}</td>
                <td class="px-4 py-3 text-slate-600">
                  {{ book.authors.map((a) => a.name).join(', ') }}
                </td>
                <td class="px-4 py-3 text-slate-600">{{ book.category.name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ book.publisher?.name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ book.published_year ?? '—' }}</td>
              </tr>

              <tr v-if="!loading && books.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                  Nessun libro trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="meta" class="mt-4 flex items-center justify-between text-sm text-slate-600">
          <p>Pagina {{ meta.current_page }} di {{ meta.last_page }} · {{ meta.total }} libri</p>

          <div class="flex gap-2">
            <button
              :disabled="meta.current_page <= 1 || loading"
              class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
              @click="goToPage(meta.current_page - 1)"
            >
              Precedente
            </button>
            <button
              :disabled="meta.current_page >= meta.last_page || loading"
              class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
              @click="goToPage(meta.current_page + 1)"
            >
              Successiva
            </button>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>