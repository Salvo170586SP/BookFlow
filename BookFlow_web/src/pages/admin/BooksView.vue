<script setup lang="ts">
import { isAxiosError } from 'axios'
import { ref, computed, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { destroyBooks, fetchBooks } from './../../api/books'
import type { Book, PaginationMeta } from './../../types/book'
import type { Category } from './../../types/category'
import { fetchCategoriesOptions } from './../../api/categories'
import LoaderComponent from './../../components/LoaderComponent.vue'
import ConfirmDeleteModal from './../../components/ConfirmDeleteModal.vue'

const route = useRoute()
const router = useRouter()

//conferma modale eliminazione
const showModal = ref(false) // modale aperta/chiusa
const bookToDelete = ref<Book | null>(null) // quale elemento sto per eliminare
const deleting = ref(false) // richiesta in corso
const deleteError = ref<string | null>(null)

const books = ref<Book[]>([])
const categories = ref<Category[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const searchTitle = ref('')

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

const filteredBooks = computed(() => {
  const term = searchTitle.value.trim().toLowerCase()
  if (!term) return books.value
  return books.value.filter((book) => book.title.toLowerCase().includes(term))
})

function askDelete(book: Book) {
  bookToDelete.value = book
  deleteError.value = null
  showModal.value = true
}

async function deleteBook() {
  if (!bookToDelete.value) return

  deleting.value = true

  try {
    await destroyBooks(bookToDelete.value.id)
    await load()
    showModal.value = false
    bookToDelete.value = null
  } catch (e) {
    console.error(e)
    deleteError.value = isAxiosError<{ message?: string }>(e)
      ? e.response?.data?.message ?? 'Impossibile eliminare il libro. Riprova.'
      : 'Impossibile eliminare il libro. Riprova.'
  } finally {
    deleting.value = false
  }
}

// L'URL conserva i filtri e la pagina, quindi funzionano refresh e tasto "indietro"
watch([page, categoryId], load, { immediate: true })
loadCategories()
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 pb-7">Catalogo</h1>

      <LoaderComponent v-if="loading && books.length === 0" />

      <section v-else>
        <div class="space-x-5 flex items-center">
          <label for="byTitle" class="flex flex-col">
            Cerca per titolo
            <input
              type="text"
              class="border bg-white px-2"
              v-model="searchTitle"
              id="byTitle"
              placeholder="cerca per titlo..."
            />
          </label>

          <label for="" class="flex flex-col">
            Cerca per categoria
            <select
              id="categories"
              name="category"
              class="w-50 border bg-white"
              :value="categoryId ?? ''"
              @change="filterByCategory"
            >
              <option value="">-Seleziona-</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>

        </div>

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
                <th class="px-4 py-3 w-100">Autori</th>
                <th class="px-4 py-3">Categoria</th>
                <th class="px-4 py-3 w-100">Editore</th>
                <th class="px-4 py-3">Anno</th>
                <th class="px-4 py-3 text-center">Copie totali</th>
                <th class="px-4 py-3 text-center">Copie prestate</th>
                <th class="px-4 py-3 text-center">Copie rimanenti</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="book in filteredBooks" :key="book.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ book.title }}</td>
                <td class="px-4 py-3 text-slate-600 text-xs">
                  {{ book.authors.map((a) => a.name).join(', ') }}
                </td>
                <td class="px-4 py-3 text-slate-600">{{ book.category.name }}</td>
                <td class="px-4 py-3 text-slate-600 text-xs">{{ book.publisher?.name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ book.published_year ?? '—' }}</td>
                <td class="px-4 py-3 text-center text-slate-600">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg font-bold"
                  >
                    {{ book.total_copies_count }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-600">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg font-bold"
                  >
                    {{ book.borrowed_copies_count }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-600">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg font-bold"
                  >
                    {{ book.remaining_copies_count }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="flex items-center gap-2">
                    <RouterLink
                      :to="`/admin/books/${book.id}/addCopies`"
                      class="border font-semibold w-32 text-center py-1 bg-gray-50 hover:bg-gray-200 text-black"
                      >Aggiungi copie</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/editCopies`"
                      class="border font-semibold w-32 text-center py-1 bg-gray-50 hover:bg-gray-200 text-black"
                      >Modifica copie</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/addLoan`"
                      class="border font-semibold px-3 py-1 bg-gray-50 hover:bg-gray-200 text-black"
                      >Presta</RouterLink
                    >
                    <button
                      @click="askDelete(book)"
                      class="bg-red-500 hover:bg-red-600 text-white cursor-pointer px-3 py-1 border border-black"
                    >
                      Elimina
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && books.length === 0">
                <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                  Nessun libro trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <ConfirmDeleteModal v-model="showModal" :loading="deleting" @confirm="deleteBook">
          Vuoi davvero eliminare
          <strong class="capitalize">{{ bookToDelete?.title }}</strong
          >?
          <p v-if="deleteError" class="mt-3 text-sm text-red-700" role="alert">
            {{ deleteError }}
          </p>
        </ConfirmDeleteModal>

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