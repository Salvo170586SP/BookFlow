<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import type { Category, PaginationMeta } from './../../../types/category'
import { fetchCategories, destroyCategories } from './../../../api/categories'
import LoaderComponent from '@/components/LoaderComponent.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'

const route = useRoute()
const router = useRouter()

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
  await destroyCategories(category_id)
  load()
}

watch(page, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8]">
    <main class="p-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Categorie</h1>
        <RouterLink
          to="/admin/categories/create"
          class="border border-[#64704a] bg-[#64704a] px-10 py-1 text-lg font-medium text-[#f5f1e8] transition hover:bg-[#4f5b39]"
          >Crea</RouterLink
        >
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <LoaderComponent v-if="loading && categories.length === 0" admin-theme />

      <section v-else class="mt-10">
        <PaginationComponent :meta="meta" :loading="loading" admin-theme @change="goToPage" />

        <div
          class="mt-3 overflow-x-auto border border-slate-300 bg-white transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide">
              <tr>
                <th class="px-4 py-3 w-80">Nome</th>
                <th class="px-4 py-3">Libri</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="category in categories" :key="category.id">
                <td class="px-4 py-3 font-medium text-slate-900">
                  <span
                    class="inline-flex px-3 py-1 items-center justify-center rounded-lg bg-green-700/80 font-semibold text-[#f5f1e8]"
                  >
                    {{ category.name }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <span
                    class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-[#64704a] font-semibold text-[#f5f1e8]"
                  >
                    {{ category.books_count }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="text-end space-x-3">
                    <RouterLink
                      :to="`/admin/categories/edit/${category.id}`"
                      class="cursor-pointer border border-[#64704a]/60 px-3 py-1 font-medium text-[#4f5b39] transition hover:bg-[#e8dfcd]"
                    >
                      Modifica
                    </RouterLink>
                    <button
                      @click="() => deleteCategory(category.id)"
                      class="cursor-pointer border border-[#8d3827]/50 px-3 py-1 font-medium text-[#8d3827] transition hover:bg-[#f5e4de]"
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

        <PaginationComponent :meta="meta" :loading="loading" admin-theme @change="goToPage" />
      </section>
    </main>
  </div>
</template>