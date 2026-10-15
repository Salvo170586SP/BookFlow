<script setup lang="ts">
import { isAxiosError } from 'axios'
import { ref, computed, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { destroyBooks, fetchBookYears, fetchBooks } from '../../../api/books'
import type { Book, Named, PaginationMeta } from '../../../types/book'
import { fetchCategoriesOptions } from '../../../api/categories'
import LoaderComponent from '../../../components/LoaderComponent.vue'
import ConfirmDeleteModal from '../../../components/ConfirmDeleteModal.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'

const route = useRoute()
const router = useRouter()

//conferma modale eliminazione
const showModal = ref(false) // modale aperta/chiusa
const bookToDelete = ref<Book | null>(null) // quale elemento sto per eliminare
const deleting = ref(false) // richiesta in corso
const deleteError = ref<string | null>(null)

const books = ref<Book[]>([])
const categories = ref<Named[]>([])
const availableYears = ref<number[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const searchTitle = ref('')
const searchYear = ref<string | null>('')
const isDraggingTable = ref(false)

let tableDrag: { pointerId: number; startX: number; startY: number; scrollLeft: number } | null =
  null

function startTableDrag(event: PointerEvent) {
  if (event.pointerType !== 'mouse' || event.button !== 0) return

  const target = event.target
  if (
    target instanceof Element &&
    target.closest('a, button, input, select, textarea, [role="button"]')
  ) {
    return
  }

  const container = event.currentTarget
  if (!(container instanceof HTMLElement)) return

  tableDrag = {
    pointerId: event.pointerId,
    startX: event.clientX,
    startY: event.clientY,
    scrollLeft: container.scrollLeft,
  }
}

function moveTableDrag(event: PointerEvent) {
  if (!tableDrag || event.pointerId !== tableDrag.pointerId) return

  const container = event.currentTarget
  if (!(container instanceof HTMLElement)) return

  const deltaX = event.clientX - tableDrag.startX
  const deltaY = event.clientY - tableDrag.startY

  if (!isDraggingTable.value) {
    if (Math.max(Math.abs(deltaX), Math.abs(deltaY)) < 5) return
    if (Math.abs(deltaY) >= Math.abs(deltaX)) {
      tableDrag = null
      return
    }

    isDraggingTable.value = true
    container.setPointerCapture(event.pointerId)
  }

  event.preventDefault()
  container.scrollLeft = tableDrag.scrollLeft - deltaX
}

function stopTableDrag(event: PointerEvent) {
  if (!tableDrag || event.pointerId !== tableDrag.pointerId) return

  tableDrag = null
  isDraggingTable.value = false
}

async function loadCategories() {
  try {
    categories.value = await fetchCategoriesOptions()
  } catch {
    console.error('Impossibile caricare le categorie')
  }
}

async function loadYears() {
  try {
    availableYears.value = await fetchBookYears()
  } catch {
    console.error('Impossibile caricare gli anni di pubblicazione')
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
    const year = searchYear.value ? Number(searchYear.value) : undefined
    const result = await fetchBooks(
      page.value,
      categoryId.value,
      searchTitle.value.trim() || undefined,
      year,
    )

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
watch([searchTitle, searchYear], (_, __, onCleanup) => {
  const timeout = window.setTimeout(() => {
    if (page.value !== 1) {
      goToPage(1)
      return
    }

    void load()
  }, 300)

  onCleanup(() => window.clearTimeout(timeout))
})
loadCategories()
loadYears()
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 pb-7">Catalogo</h1>
        <RouterLink
          class="border border-gray-300 px-3 bg-green-400 hover:bg-green-700 font-medium text-white"
          to="/admin/books/create"
        >
          Crea
        </RouterLink>
      </div>

      <LoaderComponent v-if="loading && books.length === 0" />

      <section v-else>
        <div class="space-x-5 flex items-center">
          <label for="byTitle" class="flex flex-col">
            Cerca per titolo
            <input
              type="text"
              class="border border-gray-300 bg-white px-2"
              v-model="searchTitle"
              id="byTitle"
              placeholder="cerca per titlo..."
            />
          </label>

          <label for="categories" class="flex flex-col">
            Cerca per categoria
            <select
              id="categories"
              name="category"
              class="w-50 border border-gray-300 bg-white"
              :value="categoryId ?? ''"
              @change="filterByCategory"
            >
              <option value="">-Seleziona-</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>

          <label for="publishedYear" class="flex flex-col">
            Anno di pubblicazione
            <select
              id="publishedYear"
              v-model="searchYear"
              class="w-50 border border-gray-300 bg-white"
            >
              <option value="">Tutti gli anni</option>
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </label>
        </div>

        <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ error }}
        </p>

        <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />

        <div
          class="mt-3 cursor-grab overflow-x-hidden border border-slate-300 bg-white transition-opacity"
          :class="{ 'opacity-60': loading, 'cursor-grabbing select-none': isDraggingTable }"
          @pointerdown="startTableDrag"
          @pointermove="moveTableDrag"
          @pointerup="stopTableDrag"
          @pointercancel="stopTableDrag"
        >
          <table class="w-full min-w-max divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="min-w-56 px-4 py-3">Titolo</th>
                <th class="min-w-32 px-4 py-3 text-center">Copie totali</th>
                <th class="min-w-36 px-4 py-3 text-center">Copie rimanenti</th>
                <th class="min-w-64 px-4 py-3">Autori</th>
                <th class="min-w-40 px-4 py-3">Categoria</th>
                <th class="min-w-56 px-4 py-3">Editore</th>
                <th class="min-w-24 px-4 py-3">Anno</th>
                <th
                  class="actions-column sticky right-0 z-20 min-w-max bg-slate-50 px-4 py-3 text-end"
                >
                  Azioni
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="book in books" :key="book.id">
                <td class="min-w-56 px-4 py-3 font-medium text-slate-900">{{ book.title }}</td>
                <td class="min-w-32 px-4 py-3 text-center text-slate-600">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg font-bold"
                  >
                    {{ book.total_copies_count }}
                  </span>
                </td>
                <td class="min-w-36 px-4 py-3 text-center text-slate-600">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg font-bold"
                  >
                    {{ book.remaining_copies_count }}
                  </span>
                </td>
                <td class="min-w-64 px-4 py-3 text-xs text-slate-600">
                  {{ book.authors.map((a) => a.name).join(', ') }}
                </td>
                <td class="min-w-40 px-4 py-3 text-slate-600">{{ book.category.name }}</td>
                <td class="min-w-56 px-4 py-3 text-xs text-slate-600">
                  {{ book.publisher?.name ?? '—' }}
                </td>
                <td class="min-w-24 px-4 py-3 text-slate-600">{{ book.published_year ?? '—' }}</td>
                <td
                  class="actions-column sticky right-0 z-10 min-w-max bg-white px-4 py-3 text-slate-600"
                >
                  <div class="flex items-center gap-2">
                    <RouterLink
                      :to="`/admin/books/${book.id}/details`"
                      class="border border-gray-300 font-medium w-20 text-center py-1 hover:bg-gray-200 text-gray-700"
                      >Dettagli</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/edit`"
                      class="border border-gray-300 font-medium w-20 text-center py-1 hover:bg-gray-200 text-gray-700"
                      >Modifica</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/addCopies`"
                      class="border border-emerald-300 hover:bg-emerald-200 font-medium w-30 text-center py-1 text-emerald-700"
                      >Aggiungi copie</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/editCopies`"
                      class="border border-blue-300 font-medium w-30 text-center py-1 hover:bg-gray-200 text-blue-700"
                      >Modifica copie</RouterLink
                    >
                    <RouterLink
                      :to="`/admin/books/${book.id}/addLoan`"
                      class="border border-blue-300 font-medium text-center py-1 px-3 hover:bg-gray-200 text-blue-700"
                      >Presta</RouterLink
                    >
                    <button
                      @click="askDelete(book)"
                      class="border border-red-300 font-medium text-center py-1 px-3 hover:bg-gray-200 text-red-700"
                    >
                      Elimina
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && books.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
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

        <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />
      </section>
    </main>
  </div>
</template>

<style scoped>
.actions-column::before {
  position: absolute;
  top: 0;
  bottom: 0;
  left: -12px;
  width: 12px;
  background: linear-gradient(to right, transparent, rgba(15, 23, 42, 0.11));
  content: '';
  pointer-events: none;
}
</style>