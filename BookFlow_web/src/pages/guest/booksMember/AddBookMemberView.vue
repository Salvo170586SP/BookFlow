<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { storeBooks } from '@/api/books'
import { fetchCategoriesOptions } from '@/api/categories'
import { fetchAuthorsOptions } from '@/api/authors'
import { fetchPublishersOptions } from '@/api/publishers'
import type { Named } from '@/types/book'
import type { Publisher } from '@/types/publisher'

const router = useRouter()
const errors = ref<Record<string, string>>({})
const formError = ref<string | null>(null)
const loading = ref(false)
const categories = ref<Named[]>([])
const categoriesLoading = ref(true)
const categoriesError = ref<string | null>(null)
const publishers = ref<Publisher[]>([])
const publishersLoading = ref(true)
const publishersError = ref<string | null>(null)
const authors = ref<Named[]>([])
const authorsLoading = ref(true)
const authorsError = ref<string | null>(null)
const authorSearch = ref('')
const manualPublisher = ref(false)
const manualAuthor = ref(false)

const form = ref({
  title: '',
  isbn: '',
  description: '',
  published_year: '',
  publisher_id: '',
  new_publisher_name: '',
  category_id: '',
  author_ids: [] as number[],
  new_author_name: '',
})

const filteredAuthors = computed(() => {
  const query = authorSearch.value.trim().toLocaleLowerCase()

  return authors.value.filter(
    (author) =>
      form.value.author_ids.includes(author.id) || author.name.toLocaleLowerCase().includes(query)
  )
})

const authorValidationError = computed(
  () =>
    Object.entries(errors.value).find(
      ([field]) => field === 'author_ids' || field.startsWith('author_ids.')
    )?.[1]
)

function togglePublisherMode() {
  form.value.publisher_id = ''
  form.value.new_publisher_name = ''
  delete errors.value.publisher_id
  delete errors.value.new_publisher_name
}

function toggleAuthorMode() {
  form.value.author_ids = []
  form.value.new_author_name = ''
  authorSearch.value = ''
  for (const field of Object.keys(errors.value)) {
    if (field === 'author_ids' || field.startsWith('author_ids.')) {
      delete errors.value[field]
    }
  }
  delete errors.value.new_author_name
}

async function loadCategories() {
  categoriesLoading.value = true
  categoriesError.value = null

  try {
    categories.value = await fetchCategoriesOptions()
  } catch {
    categoriesError.value = 'Impossibile caricare le categorie.'
  } finally {
    categoriesLoading.value = false
  }
}

async function loadPublishers() {
  publishersLoading.value = true
  publishersError.value = null

  try {
    publishers.value = await fetchPublishersOptions()
  } catch {
    publishersError.value = 'Impossibile caricare gli editori.'
  } finally {
    publishersLoading.value = false
  }
}

async function loadAuthors() {
  authorsLoading.value = true
  authorsError.value = null

  try {
    authors.value = await fetchAuthorsOptions()
  } catch {
    authorsError.value = 'Impossibile caricare gli autori.'
  } finally {
    authorsLoading.value = false
  }
}

async function loadOptions() {
  await Promise.all([loadCategories(), loadPublishers(), loadAuthors()])
}

onMounted(loadOptions)

async function submit() {
  loading.value = true
  errors.value = {}
  formError.value = null

  try {
    await storeBooks(form.value)
    await router.push('/books')
  } catch (error) {
    if (axios.isAxiosError<{ errors?: Record<string, string[]>; message?: string }>(error)) {
      const validationErrors = error.response?.data?.errors

      if (error.response?.status === 422 && validationErrors) {
        errors.value = Object.fromEntries(
          Object.entries(validationErrors).map(([field, messages]) => [
            field,
            messages[0] ?? 'Valore non valido.',
          ])
        )
      } else {
        formError.value = error.response?.data?.message ?? 'Impossibile salvare il libro. Riprova.'
      }
    } else {
      formError.value = 'Impossibile salvare il libro. Riprova.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8] text-[#242820]">
    <main class="mx-auto max-w-6xl px-5 pb-12 pt-10 sm:px-8 sm:pb-16">
      <div class="mb-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
          <p
            class="flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
          >
            <span class="size-2 bg-[#d45b3e]"></span>
            Dona un libro
          </p>
          <RouterLink
            to="/dashboard"
            class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
          >
            ← Torna alla dashboard
          </RouterLink>
        </div>
        <h1 class="max-w-3xl text-4xl font-black leading-[1.02] tracking-[-0.05em] sm:text-6xl">
          Condividi una storia<span class="font-serif font-normal italic text-[#d45b3e]">.</span>
        </h1>
        <p class="mt-4 max-w-2xl leading-7 text-[#65675e]">
          Aggiungi i dettagli del libro che vuoi donare alla biblioteca.
        </p>
      </div>

      <form @submit.prevent="submit" class="border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-7">
        <div class="mb-6 flex items-center justify-between border-b border-[#242820]/25 pb-4">
          <h2 class="text-xs font-bold uppercase tracking-[0.18em]">Dettagli del libro</h2>
          <span class="text-xs font-bold text-[#64704a]">BookFlow / Soci</span>
        </div>

        <div v-if="formError" class="mb-5 text-sm text-[#8d3827]" role="alert">
          {{ formError }}
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <div class="space-y-4">
            <label for="title" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Titolo*</span
              >
              <input
                type="text"
                id="title"
                v-model="form.title"
                required
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              />
            </label>
            <span v-if="errors.title" class="text-sm text-[#8d3827]">{{ errors.title }}</span>

            <label for="description" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Descrizione</span
              >
              <textarea
                id="description"
                v-model="form.description"
                rows="5"
                class="resize-y border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              ></textarea>
            </label>
            <span v-if="errors.description" class="text-sm text-[#8d3827]">{{
              errors.description
            }}</span>

            <label for="published_year" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Data di pubblicazione</span
              >
              <input
                type="number"
                id="published_year"
                v-model="form.published_year"
                min="1900"
                max="{{ date('Y') }}"
                step="1"
                placeholder="Es. 2021"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              />
            </label>
            <span v-if="errors.published_year" class="text-sm text-[#8d3827]">{{
              errors.published_year
            }}</span>

            <label for="isbn" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Codice ISBN</span
              >
              <input
                type="text"
                id="isbn"
                v-model="form.isbn"
                required
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              />
            </label>
            <span v-if="errors.isbn" class="text-sm text-[#8d3827]">{{ errors.isbn }}</span>

            <div class="flex items-end gap-3">
              <label
                id="manualPublisher"
                class="flex items-center justify-center text-sm w-7 h-7 cursor-pointer border border-[#242820]/25 transition-colors"
                :class="manualPublisher ? 'bg-emerald-500' : 'bg-white hover:bg-emerald-300'"
              >
                <input
                  v-model="manualPublisher"
                  class="hidden"
                  id="manualPublisher"
                  type="checkbox"
                  @change="togglePublisherMode"
                />
                <svg
                  v-if="manualPublisher"
                  class="w-5 h-5 text-white"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <path d="M4 10.5l4 4 8-9" />
                </svg>
              </label>
              <div class="font-medium text">Clicca qui se l'editore non è in lista</div>
            </div>

            <label v-if="!manualPublisher" for="publisher_id" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Editore</span
              >
              <select
                id="publisher_id"
                v-model="form.publisher_id"
                :disabled="publishersLoading || publishersError !== null || publishers.length === 0"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <option value="">- Seleziona -</option>
                <option
                  v-for="publisher in publishers"
                  :key="publisher.id"
                  :value="String(publisher.id)"
                >
                  {{ publisher.name }}
                </option>
              </select>
            </label>
            <span v-if="!manualPublisher && errors.publisher_id" class="text-sm text-[#8d3827]">{{
              errors.publisher_id
            }}</span>
            <label v-if="manualPublisher" for="new_publisher_name" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Editore</span
              >
              <input
                id="new_publisher_name"
                v-model="form.new_publisher_name"
                type="text"
                maxlength="255"
                placeholder="Nome dell'editore"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              />
            </label>
            <span
              v-if="manualPublisher && errors.new_publisher_name"
              class="text-sm text-[#8d3827]"
              >{{ errors.new_publisher_name }}</span
            >
            <div
              v-if="!manualPublisher && publishersLoading"
              class="text-sm text-[#65675e]"
              role="status"
            >
              Caricamento editori...
            </div>
            <div
              v-else-if="!manualPublisher && publishersError"
              class="text-sm text-[#8d3827]"
              role="alert"
            >
              {{ publishersError }}
              <button type="button" class="ml-1 underline" @click="loadPublishers">Riprova</button>
            </div>
            <div
              v-else-if="!manualPublisher && publishers.length === 0"
              class="text-sm text-[#65675e]"
            >
              Nessun editore disponibile.
            </div>

            <label for="category_id" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Categoria</span
              >
              <select
                id="category_id"
                v-model="form.category_id"
                required
                :disabled="categoriesLoading || categoriesError !== null || categories.length === 0"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <option value="">- Seleziona -</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="String(category.id)"
                >
                  {{ category.name }}
                </option>
              </select>
            </label>
            <span v-if="errors.category_id" class="text-sm text-[#8d3827]">{{
              errors.category_id
            }}</span>
            <div v-if="categoriesLoading" class="text-sm text-[#65675e]" role="status">
              Caricamento categorie...
            </div>
            <div v-else-if="categoriesError" class="text-sm text-[#8d3827]" role="alert">
              {{ categoriesError }}
              <button type="button" class="ml-1 underline" @click="loadCategories">Riprova</button>
            </div>
            <div v-else-if="categories.length === 0" class="text-sm text-[#8d3827]" role="alert">
              Nessuna categoria disponibile.
            </div>
          </div>

          <div class="flex flex-col">
            <div class="flex items-end gap-3 mb-5">
              <label
                id="manualAuthor"
                class="flex items-center justify-center text-sm w-7 h-7 cursor-pointer border border-[#242820]/25 transition-colors"
                :class="manualAuthor ? 'bg-emerald-500' : 'bg-white hover:bg-emerald-300'"
              >
                <input
                  v-model="manualAuthor"
                  class="hidden"
                  type="checkbox"
                  @change="toggleAuthorMode"
                />
                <svg
                  v-if="manualAuthor"
                  class="w-5 h-5 text-white"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <path d="M4 10.5l4 4 8-9" />
                </svg>
              </label>
              <div class="font-medium text">Clicca qui se l'autore non è in lista</div>
            </div>

            <template v-if="!manualAuthor">
              <label for="author-search" class="flex flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                  >Autori</span
                >
                <input
                  id="author-search"
                  v-model="authorSearch"
                  type="search"
                  placeholder="Digita il nome di un autore"
                  :disabled="authorsLoading || authorsError !== null || authors.length === 0"
                  class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e] disabled:cursor-not-allowed disabled:opacity-60"
                />
              </label>

              <label for="author_ids" class="mt-4 flex flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                  >Seleziona autori</span
                >
                <select
                  id="author_ids"
                  v-model="form.author_ids"
                  multiple
                  :disabled="authorsLoading || authorsError !== null || authors.length === 0"
                  class="min-h-56 border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2 outline-none focus:border-[#d45b3e] disabled:cursor-not-allowed disabled:opacity-60"
                  aria-describedby="authors-help"
                >
                  <option v-for="author in filteredAuthors" :key="author.id" :value="author.id">
                    {{ author.name }}
                  </option>
                </select>
              </label>
              <button
                type="button"
                class="mt-2 self-start text-sm font-semibold text-[#8d3827] underline underline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="form.author_ids.length === 0"
                @click="form.author_ids = []"
              >
                Rimuovi tutti gli autori
              </button>
              <div class="mt-4">
                <span id="authors-help" class="text-sm leading-6 text-[#65675e]">
                  Puoi lasciare il campo vuoto. Cerca per nome e seleziona uno o più autori
                  (Ctrl/Cmd + clic). Gli autori già selezionati restano visibili anche quando non
                  corrispondono alla ricerca.
                </span>
                <span v-if="authorValidationError" class="mt-2 block text-sm text-[#8d3827]">{{
                  authorValidationError
                }}</span>
                <div v-if="authorsLoading" class="mt-2 text-sm text-[#65675e]" role="status">
                  Caricamento autori...
                </div>
                <div v-else-if="authorsError" class="mt-2 text-sm text-[#8d3827]" role="alert">
                  {{ authorsError }}
                  <button type="button" class="ml-1 underline" @click="loadAuthors">Riprova</button>
                </div>
                <div
                  v-else-if="authors.length === 0"
                  class="mt-2 text-sm text-[#8d3827]"
                  role="alert"
                >
                  Nessun autore disponibile.
                </div>
              </div>
            </template>
            <label v-else for="new_author_name" class="flex flex-col gap-2">
              <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]"
                >Autore</span
              >
              <input
                id="new_author_name"
                v-model="form.new_author_name"
                type="text"
                maxlength="255"
                placeholder="Nome e cognome dell'autore"
                class="border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
              />
            </label>
            <span
              v-if="manualAuthor && errors.new_author_name"
              class="mt-1 text-sm text-[#8d3827]"
              >{{ errors.new_author_name }}</span
            >
          </div>
        </div>

        <div class="mt-7 flex justify-end border-t border-[#242820]/25 pt-5">
          <button
            :disabled="
              loading || categoriesLoading || categoriesError !== null || categories.length === 0
            "
            class="border border-[#d45b3e]/40 bg-[#d45b3e]/15 px-5 py-2.5 text-sm font-bold transition-colors hover:bg-[#d45b3e]/25 disabled:cursor-not-allowed disabled:opacity-50"
          >
            Crea
          </button>
        </div>
      </form>
    </main>
  </div>
</template>
