<script setup lang="ts">
import { ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { fetchAuthor } from './../../../api/authors'
import type { Author } from './../../../types/author'
import LoaderComponent from '@/components/LoaderComponent.vue'

const route = useRoute()
const author = ref<Author | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
let requestId = 0

async function load(): Promise<void> {
  const currentRequest = ++requestId

  const authorId = Number(route.params.id)
  author.value = null
  error.value = null

  if (!Number.isInteger(authorId) || authorId < 1) {
    error.value = 'ID autore non valido.'
    loading.value = false
    return
  }

  loading.value = true
  try {
    const result = await fetchAuthor(authorId)
    if (currentRequest !== requestId) return
    author.value = result
  } catch {
    if (currentRequest !== requestId) return

    error.value = "Impossibile caricare i dettagli dell'autore."
  } finally {
    if (currentRequest === requestId) loading.value = false
  }
}

watch(() => route.params.id, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dettagli Autore</h1>
        <RouterLink to="/admin/authors">Indietro</RouterLink>
      </div>

      <LoaderComponent v-if="loading" />

      <p v-else-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <section v-else-if="author" class="mt-6 border border-slate-300 bg-white p-6">
        <h2 class="text-lg font-semibold text-slate-900">{{ author.name }}</h2>
        <p class="mt-3 whitespace-pre-wrap text-sm text-slate-600">
          {{ author.bio || 'Nessuna biografia disponibile.' }}
        </p>
      </section>

      <section v-else class="mt-6 border border-slate-300 bg-white p-6">
        <p class="text-sm text-slate-600">Autore non trovato.</p>
      </section>

      <section v-if="author" class="mt-6 border border-slate-300 bg-white p-6">
        <p class="text-lg font-semibold text-slate-900 mb-5">Libri</p>
        <div v-if="author.books.length" class="mt-3 flex flex-wrap space-y-2">
          <div
            v-for="(book, index) in author.books"
            :key="book.id"
            class="me-2 text-sm font-semibold text-slate-700"
          >
            "{{ book.title }}" {{ index < author.books.length - 1 ? ',' : '' }}
          </div>
        </div>
        <p v-else class="mt-3 text-sm text-slate-500">Questo autore non ha libri associati.</p>
      </section>
    </main>
  </div>
</template>