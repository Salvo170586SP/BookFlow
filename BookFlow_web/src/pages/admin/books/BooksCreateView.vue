<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { storeBooks } from './../../../api/books'
import { fetchCategoriesOptions } from './../../../api/categories'
import { fetchAuthorsOptions } from './../../../api/authors'
import { fetchPublishersOptions } from './../../../api/publishers'
import type { Named } from './../../../types/book'
import type { Publisher } from './../../../types/publisher'

const router = useRouter()
const errors = ref<Record<string, string[]>>({})
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
  new_publisher_name: '',
  category_id: '',
  author_ids: [] as number[],
  new_author_name: '',
})

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

  try {
    await storeBooks(form.value)
    router.push('/admin/books')
  } catch (e) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      errors.value = e.response.data.errors
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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Crea Libro</h1>
        <RouterLink to="/admin/books" class="font-semibold">Indietro</RouterLink>
      </div>
      <form @submit.prevent="submit" class="w-full p-5 border border-slate-300 bg-white mx-auto">
        <div class="grid grid-cols-2 gap-3">
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
            <span class="mt-1 text-sm text-red-600">{{ errors.title }}</span>

            <label for="description" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Descrizione</span>
              <textarea
                id="description"
                v-model="form.description"
                class="bg-gray-100 border border-gray-300 px-2"
              ></textarea>
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.description }}</span>

            <label for="published_year" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Data di pubblicazione</span>
              <input
                type="number"
                id="published_year"
                v-model="form.published_year"
                min="1900"
                max="{{ date('Y') }}"
                step="1"
                placeholder="Es. 2021"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.published_year }}</span>

            <label for="isbn" class="flex flex-col font-semibold mb-2">
              <span class="text-sm">Codice ISBN</span>
              <input
                type="text"
                id="isbn"
                v-model="form.isbn"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span class="mt-1 text-sm text-red-600">{{ errors.isbn }}</span>

            <label class="mb-2 flex items-center gap-2 text-sm">
              <input v-model="manualPublisher" type="checkbox" @change="togglePublisherMode" />
              <span>Inserisci l'editore manualmente</span>
            </label>
            <label
              v-if="!manualPublisher"
              for="publisher_id"
              class="mb-2 flex flex-col font-semibold"
            >
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
            <span v-if="!manualPublisher" class="mt-1 text-sm text-red-600">{{
              errors.publisher_id
            }}</span>
            <label
              v-if="manualPublisher"
              for="new_publisher_name"
              class="mb-2 flex flex-col font-semibold"
            >
              <span class="text-sm">Editore</span>
              <input
                id="new_publisher_name"
                v-model="form.new_publisher_name"
                type="text"
                maxlength="255"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span v-if="manualPublisher" class="mt-1 text-sm text-red-600">{{
              errors.new_publisher_name
            }}</span>
            <div
              v-if="!manualPublisher && publishersLoading"
              class="mt-1 text-sm text-slate-600"
              role="status"
            >
              Caricamento editori...
            </div>
            <div
              v-else-if="!manualPublisher && publishersError"
              class="mt-1 text-sm text-red-600"
              role="alert"
            >
              {{ publishersError }}
              <button type="button" class="ml-1 underline" @click="loadPublishers">Riprova</button>
            </div>
            <div
              v-else-if="!manualPublisher && publishers.length === 0"
              class="mt-1 text-sm text-slate-600"
            >
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
            <span class="mt-1 text-sm text-red-600">{{ errors.category_id }}</span>
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
            <label class="mt-3 flex items-center gap-2 text-sm">
              <input v-model="manualAuthor" type="checkbox" @change="toggleAuthorMode" />
              <span>Inserisci l'autore manualmente</span>
            </label>
            <template v-if="!manualAuthor">
              <label for="author-search" class="mt-3 flex flex-col font-semibold">
                <span class="text-sm">Autori (facoltativo)</span>
                <span class="text-xs">Cerca autore</span>
                <input
                  id="author-search"
                  v-model="authorSearch"
                  type="search"
                  placeholder="Digita il nome di un autore"
                  :disabled="authorsLoading || authorsError !== null || authors.length === 0"
                  class="bg-gray-100 border border-gray-300 px-2"
                />
              </label>

              <label for="author_ids" class="mb-2 flex flex-col font-semibold">
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
                class="mt-1 self-start text-sm underline text-red-500"
                :disabled="form.author_ids.length === 0"
                @click="form.author_ids = []"
              >
                Rimuovi tutti gli autori
              </button>
              <div>
                <span id="authors-help" class="mt-1 text-xs text-slate-600">
                  Puoi lasciare il campo vuoto. Cerca per nome e seleziona uno o più autori (Ctrl/Cmd
                  + clic). Gli autori già selezionati restano visibili anche quando non corrispondono
                  alla ricerca.
                </span>
                <span class="mt-1 text-sm text-red-600">{{ authorValidationError }}</span>
                <div v-if="authorsLoading" class="mt-1 text-sm text-slate-600" role="status">
                  Caricamento autori...
                </div>
                <div v-else-if="authorsError" class="mt-1 text-sm text-red-600" role="alert">
                  {{ authorsError }}
                  <button type="button" class="ml-1 underline" @click="loadAuthors">Riprova</button>
                </div>
                <div
                  v-else-if="authors.length === 0"
                  class="mt-1 text-sm text-red-600"
                  role="alert"
                >
                  Nessun autore disponibile.
                </div>
              </div>
            </template>
            <label v-else for="new_author_name" class="mt-3 flex flex-col font-semibold">
              <span class="text-sm">Autore</span>
              <input
                id="new_author_name"
                v-model="form.new_author_name"
                type="text"
                maxlength="255"
                class="bg-gray-100 border border-gray-300 px-2"
              />
            </label>
            <span v-if="manualAuthor" class="mt-1 text-sm text-red-600">{{
              errors.new_author_name
            }}</span>
          </div>
        </div>

        <div class="mt-5 text-end">
          <button
            :disabled="
              loading || categoriesLoading || categoriesError !== null || categories.length === 0
            "
            class="bg-green-400 hover:bg-green-700 cursor-pointer border border-gray-300 px-3 py-1 text-white font-medium"
          >
            Crea
          </button>
        </div>
      </form>
    </main>
  </div>
</template>