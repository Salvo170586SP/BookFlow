<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import type { Category, PaginationMeta } from '../../types/category'
import { fetchCategories } from '../../api/categories'
import { useCategoryStore } from '../../stores/category'

const route = useRoute()
const router = useRouter()
const categoryStore = useCategoryStore()

const categories = ref<Category[]>([])
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
    const result = await fetchCategories(page.value)

    // Se nel frattempo è partita una richiesta più recente, questa risposta è vecchia
    if (requestId !== lastRequest) return

    if (result.meta.last_page > 0 && page.value > result.meta.last_page) {
      router.replace({ query: { ...route.query, page: result.meta.last_page } })
      return
    }

    categories.value = result.data
    meta.value = result.meta
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare le categorie. Riprova.'
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

async function deleteCategory(category_id: any) {
  await categoryStore.destroy(category_id)
  load()
}

watch(page, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Categorie</h1>
        <RouterLink
          to="/admin/categories/create"
          class="bg-green-500 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded-lg"
          >Crea</RouterLink
        >
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
                <th class="px-4 py-3">Libri</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="category in categories" :key="category.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ category.name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ category.books_count }}</td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="text-end space-x-3">
                    <RouterLink
                      :to="`/admin/categories/edit/${category.id}`"
                      class="bg-blue-500 hover:bg-blue-600 text-white cursor-pointer px-3 py-1 rounded-lg"
                    >
                      Modifica
                    </RouterLink>
                    <button
                      @click="() => deleteCategory(category.id)"
                      class="bg-red-500 hover:bg-red-600 text-white cursor-pointer px-3 py-1 rounded-lg"
                    >
                      Elimina
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="categories.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Nessuna categoria trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="meta" class="mt-4 flex items-center justify-between text-sm text-slate-600">
          <p>Pagina {{ meta.current_page }} di {{ meta.last_page }} · {{ meta.total }} categorie</p>

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