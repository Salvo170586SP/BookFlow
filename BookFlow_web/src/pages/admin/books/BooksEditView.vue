<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { fetchBook, updateBook } from './../../../api/books'
import { fetchCategoriesOptions } from './../../../api/categories'
import { fetchAuthorsOptions } from './../../../api/authors'
import { fetchPublishersOptions } from './../../../api/publishers'
import LoaderComponent from '@/components/LoaderComponent.vue'
import type { Book, Named } from './../../../types/book'
import type { Publisher } from './../../../types/publisher'

const router = useRouter()
const route = useRoute()
const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const loadingBook = ref(true)
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
const book = ref<Book | null>(null)
const formError = ref<string | null>(null)

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
    )?.[1]?.[0]
)

const form = ref({
  title: '',
  isbn: '',
  description: '',
  published_year: '',
  publisher_id: '',
  category_id: '',
  author_ids: [] as number[],
})

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

onMounted(async () => {
  loadingBook.value = true

  try {
    const id = Number(route.params.id)
    await loadOptions()

    const currentBook = await fetchBook(id)
    book.value = currentBook
    form.value = {
      title: currentBook.title,
      isbn: currentBook.isbn,
      description: currentBook.description ?? '',
      published_year: currentBook.published_year != null ? String(currentBook.published_year) : '',
      publisher_id: currentBook.publisher?.id ? String(currentBook.publisher.id) : '',
      category_id: currentBook.category?.id ? String(currentBook.category.id) : '',
      author_ids: currentBook.authors.map((author) => author.id),
    }
  } catch {
    formError.value = 'Impossibile caricare i dati del libro.'
  } finally {
    loadingBook.value = false
  }
})

async function submit() {
  loading.value = true
  errors.value = {}
  formError.value = null

  try {
    const id = Number(route.params.id)
    await updateBook(form.value, id)
    router.push('/admin/books')
  } catch (e) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      errors.value = e.response.data.errors
    } else {
      formError.value = 'Impossibile salvare le modifiche del libro.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <div class="flex items-center justify-between mb-20">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
          Modifica libro{{ book ? `: ${book.title}` : '' }}
        </h1>
        <RouterLink to="/admin/books" class="font-semibold">Indietro</RouterLink>
      </div>

      <p v-if="formError" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ formError }}
      </p>

      <LoaderComponent v-if="loadingBook" />

      <form
        v-else
        @submit.prevent="submit"
        class="w-full p-5 border border-slate-300 bg-white mx-auto"
      >
        <div class="grid grid-cols-2 gap-5">
          <div>
            <label for="title" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Titolo*</span>
              <input
                type="text"
                id="title"
                v-model="form.title"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.title?.[0] }}</span>

            <label for="description" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Descrizione</span>
              <textarea
                id="description"
                v-model="form.description"
                class="bg-gray-100 border border-gray-300 px-2"
              ></textarea>
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.description?.[0] }}</span>

            <label for="published_year" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Data di pubblicazione</span>
              <input
                type="number"
                id="published_year"
                v-model="form.published_year"
                min="1900"
                :max="new Date().getFullYear()"
                step="1"
                placeholder="Es. 2021"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.published_year?.[0] }}</span>

            <label for="isbn" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Codice ISBN</span>
              <input
                type="text"
                id="isbn"
                v-model="form.isbn"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.isbn?.[0] }}</span>

            <label for="publisher_id" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Editore</span>
              <select
                id="publisher_id"
                v-model="form.publisher_id"
                :disabled="publishersLoading || publishersError !== null || publishers.length === 0"
                class="bg-gray-100 border border-gray-300 px-2"
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
            <span class="mt-1 text-sm text-red-600">{{ errors.publisher_id?.[0] }}</span>
            <div v-if="publishersLoading" class="mt-1 text-sm text-slate-600" role="status">
              Caricamento editori...
            </div>
            <div v-else-if="publishersError" class="mt-1 text-sm text-red-600" role="alert">
              {{ publishersError }}
              <button type="button" class="ml-1 underline" @click="loadPublishers">Riprova</button>
            </div>
            <div v-else-if="publishers.length === 0" class="mt-1 text-sm text-slate-600">
              Nessun editore disponibile.
            </div>

            <label for="category_id" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Categoria</span>
              <select
                id="category_id"
                v-model="form.category_id"
                :disabled="categoriesLoading || categoriesError !== null || categories.length === 0"
                class="bg-gray-100 border border-gray-300 px-2"
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
            <span class="mt-1 text-sm text-red-600">{{ errors.category_id?.[0] }}</span>
            <div v-if="categoriesLoading" class="mt-1 text-sm text-slate-600" role="status">
              Caricamento categorie...
            </div>
            <div v-else-if="categoriesError" class="mt-1 text-sm text-red-600" role="alert">
              {{ categoriesError }}
              <button type="button" class="ml-1 underline" @click="loadCategories">Riprova</button>
            </div>
            <div v-else-if="categories.length === 0" class="mt-1 text-sm text-red-600" role="alert">
              Nessuna categoria disponibile.
            </div>
          </div>

          <div>
            <label for="author-search" class="mt-3 flex flex-col font-semibold space-y-1">
              <span class="text-sm">Autori</span>
              <small>Cerca autore</small>
              <input
                id="author-search"
                v-model="authorSearch"
                type="search"
                placeholder="Digita il nome di un autore"
                :disabled="authorsLoading || authorsError !== null || authors.length === 0"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>

            <label for="author_ids" class="flex flex-col font-semibold space-y-2">
              <select
                id="author_ids"
                v-model="form.author_ids"
                multiple
                :disabled="authorsLoading || authorsError !== null || authors.length === 0"
                class="bg-gray-100 border border-gray-300 px-2"
                aria-describedby="authors-help"
              >
                <option v-for="author in filteredAuthors" :key="author.id" :value="author.id">
                  {{ author.name }}
                </option>
              </select>
            </label>
            <button
              type="button"
              class="mt-1 cursor-pointer self-start text-sm underline text-red-800"
              :disabled="form.author_ids.length === 0"
              @click="form.author_ids = []"
            >
              Rimuovi tutti gli autori
            </button>
            <div class="mt-3">
              <small id="authors-help" class="text-slate-600">
                Puoi lasciare il campo vuoto. Cerca per nome e seleziona uno o più autori (Ctrl/Cmd
                + clic). Gli autori già selezionati restano visibili anche quando non corrispondono
                alla ricerca.
              </small>
              <span class="mt-1 text-sm text-red-600">{{ authorValidationError }}</span>
              <div v-if="authorsLoading" class="mt-1 text-sm text-slate-600" role="status">
                Caricamento autori...
              </div>
              <div v-else-if="authorsError" class="mt-1 text-sm text-red-600" role="alert">
                {{ authorsError }}
                <button type="button" class="ml-1 underline" @click="loadAuthors">Riprova</button>
              </div>
              <div v-else-if="authors.length === 0" class="mt-1 text-sm text-red-600" role="alert">
                Nessun autore disponibile.
              </div>
            </div>
          </div>
        </div>

        <div class="mt-5 text-center">
          <button
            :disabled="
              loading ||
              categoriesLoading ||
              categoriesError !== null ||
              categories.length === 0 ||
              publishersLoading ||
              publishersError !== null ||
              publishers.length === 0
            "
            class="bg-blue-400 hover:bg-blue-700 cursor-pointer border border-gray-300 px-3 py-1 text-white font-medium"
          >
            {{ loading ? 'Salvataggio...' : 'Salva modifiche' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>
