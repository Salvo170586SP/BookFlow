<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import type { Author } from './../../../types/author'
import { fetchAuthors } from './../../../api/authors'
import type { PaginationMeta } from './../../../types/book'
import LoaderComponent from '@/components/LoaderComponent.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'

const route = useRoute()
const router = useRouter()

const authors = ref<Author[]>([])
const meta = ref<PaginationMeta | null>(null)

const loading = ref(false)
const error = ref<string | null>(null)
const searchName = ref('')

const page = computed(() => Math.max(Number(route.query.page) || 1, 1))

// Contatore delle richieste: serve a ignorare le risposte arrivate in ritardo
let lastRequest = 0

async function load() {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null

  try {
    const result = await fetchAuthors(page.value, searchName.value.trim() || undefined)

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
watch(searchName, (_, __, onCleanup) => {
  const timeout = window.setTimeout(() => {
    if (page.value !== 1) {
      goToPage(1)
      return
    }

    void load()
  }, 300)

  onCleanup(() => window.clearTimeout(timeout))
})
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Autori</h1>
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <LoaderComponent v-if="loading && authors.length === 0" />

      <section v-else>
        <label for="byName" class="flex flex-col mt-5">
          Cerca 
          <input
            type="text"
            class="border border-gray-300 bg-white px-2"
            v-model="searchName"
            id="byName"
            placeholder="cerca per nome..."
          />
        </label>

        <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />

        <div
          class="mt-3 overflow-x-auto border border-slate-300 bg-white transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 w-80">Nome</th>
                <th class="px-4 py-3">Bio</th>
                <th class="px-4 py-3 text-end">Libri</th>
                <th class="px-4 py-3 text-end">Azioni</th>
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
                <td class="px-4 py-3 font-medium text-slate-900 text-end">
                  <div>
                    <RouterLink
                      class="border border-gray-400 px-3 py-1 hover:bg-gray-200 text-gray-700"
                      :to="`/admin/authors/${author.id}/details`"
                      >Dettagli</RouterLink
                    >
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && authors.length === 0">
                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                  Nessun autore trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <PaginationComponent :meta="meta" :loading="loading" @change="goToPage" />
      </section>
    </main>
  </div>
</template>