<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Author } from '../../types/author'
import { fetchAuthors } from '../../api/authors'
import type { PaginationMeta } from '../../types/book'

const route = useRoute()
const router = useRouter()

const authors = ref<Author[]>([])
const meta = ref<PaginationMeta | null>(null)

const loading = ref(false)
const error = ref<string | null>(null)

const page = computed(() => Math.max(Number(route.query.page) || 1, 1))

// Contatore delle richieste: serve a ignorare le risposte arrivate in ritardo
let lastRequest = 0

async function load() {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null

  try {
    const result = await fetchAuthors(page.value)

    // Se nel frattempo è partita una richiesta più recente, questa risposta è vecchia
    if (requestId !== lastRequest) return

    if (result.meta.last_page > 0 && page.value > result.meta.last_page) {
      router.replace({ query: { ...route.query, page: result.meta.last_page } })
      return
    }

    authors.value = result.data
    meta.value = result.meta
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare gli autori. Riprova.'
    }
  } finally {
    if (requestId === lastRequest) {
      loading.value = false
    }
  }
}

function goToPage(nextPage: number) {
  router.push({ query: { ...route.query, page: nextPage } })
}

watch(page, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Autori</h1>
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <div v-if="loading" class="flex flex-col justify-center items-center">
        <div
          class="size-8 animate-spin rounded-full border-4 border-solid border-gray-300 border-t-black"
        ></div>
        <span class="text-black">Caricamento...</span>
      </div>

      <section v-else>
        <div
          class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 w-80">Nome</th>
                <th class="px-4 py-3">Bio</th>
                <th class="px-4 py-3 text-end">Libri</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="author in authors" :key="author.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ author.name }}</td>
                <td class="px-4 py-3 text-slate-900">
                  {{
                    author.bio
                      ? author.bio.length > 20
                        ? author.bio.substring(0, 20) + '...'
                        : author.bio
                      : ''
                  }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900 text-end">
                  <span
                    class="inline-flex w-6 h-6 bg-gray-200 items-center justify-center rounded-lg"
                  >
                    {{ author.books.length }}
                  </span>
                </td>
              </tr>

              <tr v-if="!loading && authors.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Nessun autore trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="meta" class="mt-4 flex items-center justify-between text-sm text-slate-600">
          <p>Pagina {{ meta.current_page }} di {{ meta.last_page }} · {{ meta.total }} autori</p>

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