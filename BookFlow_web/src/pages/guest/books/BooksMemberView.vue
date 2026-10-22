<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { fetchBookYears, fetchBooks } from '../../../api/books'
import type { Book, Named, PaginationMeta } from '../../../types/book'
import { fetchCategoriesOptions } from '../../../api/categories'
import LoaderComponent from '../../../components/LoaderComponent.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'

const route = useRoute()
const router = useRouter()
const books = ref<Book[]>([])
const categories = ref<Named[]>([])
const availableYears = ref<number[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const searchTitle = ref('')
const searchYear = ref<string | null>('')

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
      year
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
  <div class="min-h-full bg-[#f5f1e8] text-[#242820]">
    <main>
      <section class="mx-auto max-w-6xl px-5 pb-12 pt-10 sm:px-8 sm:pb-16">
        <div class="mb-10">
          <p
            class="mb-4 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
          >
            <span class="size-2 bg-[#d45b3e]"></span>
            La biblioteca dei soci
          </p>
          <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <h1 class="text-5xl font-black leading-[0.98] tracking-[-0.06em] sm:text-6xl">
                Il nostro catalogo<span class="font-serif font-normal italic text-[#d45b3e]"
                  >.</span
                >
              </h1>
              <p class="mt-4 max-w-xl leading-7 text-[#65675e]">
                Esplora le storie disponibili nella biblioteca e trova la tua prossima lettura.
              </p>
            </div>
            <RouterLink
              to="/dashboard"
              class="w-fit border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
            >
              Torna alla dashboard
            </RouterLink>
          </div>
        </div>

        <section
          aria-label="Filtri del catalogo"
          class="border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-6"
        >
          <div class="mb-5 flex items-center justify-between border-b border-[#242820]/25 pb-4">
            <h2 class="text-xs font-bold uppercase tracking-[0.18em]">Trova un libro</h2>
            <span class="text-xs font-bold text-[#64704a]">BookFlow / Catalogo</span>
          </div>
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label for="byTitle" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Titolo</span
              >
              <input
                id="byTitle"
                v-model="searchTitle"
                type="search"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
                placeholder="Cerca un titolo..."
              />
            </label>

            <label for="categories" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Categoria</span
              >
              <select
                id="categories"
                name="category"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
                :value="categoryId ?? ''"
                @change="filterByCategory"
              >
                <option value="">Tutte le categorie</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </label>

            <label for="publishedYear" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Anno di pubblicazione</span
              >
              <select
                id="publishedYear"
                v-model="searchYear"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              >
                <option value="">Tutti gli anni</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </label>
          </div>
        </section>

        <p
          v-if="error"
          role="alert"
          class="mt-6 border border-[#d45b3e]/40 bg-[#d45b3e]/5 p-5 font-medium text-[#8d3827]"
        >
          {{ error }}
        </p>

        <div v-if="loading && books.length === 0" role="status" class="py-16">
          <LoaderComponent />
        </div>

        <section v-else aria-label="Libri in catalogo" class="mt-8">
          <div class="mb-4 flex items-center justify-between gap-4">
            <h2 class="text-xl font-black tracking-tight sm:text-2xl">Storie da scoprire</h2>
            <p v-if="meta" class="text-xs font-semibold text-[#65675e]">
              {{ meta.total }} {{ meta.total === 1 ? 'libro' : 'libri' }}
            </p>
          </div>

          <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />

          <div
            v-if="books.length > 0"
            class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            :class="{ 'opacity-60': loading }"
          >
            <article
              v-for="(book, index) in books"
              :key="book.id"
              class="flex min-h-80 flex-col border border-[#242820]/25 bg-[#ede7da] p-4"
            >
              <div class="flex items-start gap-4">
                <img
                  v-if="book.cover"
                  :src="book.cover"
                  :alt="`Copertina di ${book.title}`"
                  class="aspect-[3/4] w-24 shrink-0 border border-[#242820]/20 object-cover"
                />
                <div
                  v-else
                  role="img"
                  :aria-label="`Segnaposto copertina per ${book.title}`"
                  class="flex aspect-[3/4] w-24 shrink-0 flex-col items-center justify-center gap-3 border border-[#242820]/20 p-2 text-center text-[#f5f1e8]"
                  :class="[
                    ['bg-[#64704a]', 'bg-[#d45b3e]', 'bg-[#d5ad56]'][index % 3],
                    index % 3 === 2 ? 'text-[#242820]' : '',
                  ]"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="size-8" aria-hidden="true">
                    <path
                      d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                      stroke="currentColor"
                      stroke-width="1.4"
                    />
                  </svg>
                  <span class="text-[9px] font-bold uppercase leading-tight tracking-[0.12em]"
                    >Copertina<br />in arrivo</span
                  >
                </div>
                <div class="min-w-0 pt-1">
                  <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#64704a]">
                    {{ book.category.name }}
                  </p>
                  <h3 class="mt-2 font-serif text-xl font-bold leading-tight">{{ book.title }}</h3>
                  <p class="mt-2 text-sm leading-5 text-[#65675e]">
                    {{
                      book.authors.map((author) => author.name).join(', ') || 'Autore non indicato'
                    }}
                  </p>
                </div>
              </div>

              <div class="mt-5 border-t border-[#242820]/20 pt-4">
                <div class="flex flex-wrap justify-between gap-2 text-xs text-[#65675e]">
                  <span>{{ book.publisher?.name ?? 'Editore non indicato' }}</span>
                  <span>{{ book.published_year ?? 'Anno non indicato' }}</span>
                </div>
                <p class="mt-4 text-xs font-semibold text-[#55594e]">
                  <span
                    class="mr-1 inline-block size-2"
                    :class="book.available_copies_count > 0 ? 'bg-[#64704a]' : 'bg-[#d45b3e]'"
                  ></span>
                  {{
                    book.available_copies_count > 0
                      ? `${book.available_copies_count} copie disponibili`
                      : 'Nessuna copia disponibile'
                  }}
                </p>
              </div>

              <RouterLink
                :to="`/books/${book.id}/details`"
                class="mt-auto flex items-center justify-between border border-[#d45b3e]/40 bg-[#d45b3e]/10 px-4 py-2.5 text-sm font-bold transition-colors hover:bg-[#d45b3e]/20"
              >
                Scopri il libro
                <span aria-hidden="true">→</span>
              </RouterLink>
            </article>
          </div>
          <div
            v-else-if="!loading && !error"
            class="mt-4 border border-[#242820]/25 bg-[#ede7da] p-8 text-center"
          >
            <p class="font-serif text-2xl italic text-[#65675e]">Nessun libro trovato.</p>
            <p class="mt-2 text-sm text-[#65675e]">Prova a modificare i filtri di ricerca.</p>
          </div>

          <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />
        </section>
      </section>
    </main>
  </div>
</template>
 