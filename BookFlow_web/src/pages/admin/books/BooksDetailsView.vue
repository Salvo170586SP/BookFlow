<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchBook } from '../../../api/books'
import type { Book } from '../../../types/book'
import LoaderComponent from '../../../components/LoaderComponent.vue'

const route = useRoute()

const loading = ref(false)
const error = ref<string | null>(null)

const expandedCopyIds = ref<number[]>([])

function isCopyExpanded(copyId: number): boolean {
  return expandedCopyIds.value.includes(copyId)
}

function toggleCopyDetails(copyId: number): void {
  if (isCopyExpanded(copyId)) {
    expandedCopyIds.value = expandedCopyIds.value.filter((id) => id !== copyId)
    return
  }

  expandedCopyIds.value = [...expandedCopyIds.value, copyId]
}

const book = ref<Book | null>(null)
let requestId = 0

const loanedCopies = computed(() => {
  return (book.value?.copies ?? []).filter((copy) => (copy.loans ?? []).length > 0)
})

function formatLoanDate(date?: string | null): string {
  if (!date) return '-'

  return new Intl.DateTimeFormat('it-IT', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

async function load(): Promise<void> {
  const currentRequest = ++requestId

  const bookId = Number(route.params.id)
  book.value = null
  error.value = null

  if (!Number.isInteger(bookId) || bookId < 1) {
    error.value = 'ID book non valido.'
    loading.value = false
    return
  }

  loading.value = true
  try {
    const result = await fetchBook(bookId)
    if (currentRequest !== requestId) return
    book.value = result
  } catch {
    if (currentRequest !== requestId) return

    error.value = 'Impossibile caricare i dettagli del libro.'
  } finally {
    if (currentRequest === requestId) loading.value = false
  }
}

watch(() => route.params.id, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 pb-7">
        Dettagli di <span v-if="book">{{ book.title }}</span>
      </h1>
      <RouterLink
        :to="`/admin/books`"
        class="border border-gray-300 font-semibold w-32 text-center px-3 bg-gray-50 hover:bg-gray-200 text-black"
        >Indietro</RouterLink
      >

      <LoaderComponent v-if="loading" />

      <section v-else>
        <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ error }}
        </p>

        <div v-if="book" class="mt-6 border border-slate-300 bg-white p-6">
          <h2 class="text-lg font-semibold text-slate-900 capitalize">{{ book.title }}</h2>
          <div class="flex gap-50 mt-3 text-sm text-slate-600">
            <div>
              <p>
                Isbn: <span class="font-semibold">{{ book.isbn || '-' }}</span>
              </p>
              <p>
                Categoria:
                <span class="font-semibold">{{ book.category.name || '-' }}</span>
              </p>
              <p>
                Autori:
                <span class="font-semibold">{{ book.authors?.length }}</span>
              </p>
              <p>
                Editore:
                <span class="font-semibold">{{ book.publisher?.name }}</span>
              </p>
            </div>
            <div class="flex flex-col">
              <p>
                Anno di pubblicazione:
                <span class="font-semibold">{{ book.published_year || '-' }}</span>
              </p>
            </div>
            <div>
              <p>
                Copie totali:
                <span class="font-semibold">{{ book.total_copies_count || '-' }}</span>
              </p>
              <p>
                Copie prestate:
                <span class="font-semibold">{{ book.borrowed_copies_count || '-' }}</span>
              </p>
              <p>
                Copie disponibili:
                <span class="font-semibold">{{ book.available_copies_count || '-' }}</span>
              </p>
            </div>
          </div>

          <div class="mt-6 border border-slate-300 bg-white p-6">
            <h2 class="text-lg font-semibold text-slate-900 capitalize">Autori</h2>
            <div class="flex gap-5 mt-3 text-sm text-slate-600">
              <div v-for="author in book.authors" :key="author.id">
                <p class="font-semibold border px-2">
                  {{ author.name }}
                </p>
              </div>

              <div v-if="book.authors.length === 0">
                <p class="font-semibold">Nessun autore disponibile.</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="book" class="mt-6 border border-slate-300 bg-white p-6">
          <h2 class="text-lg font-semibold text-slate-900">
            Copie prestate {{ book.borrowed_copies_count }}
          </h2>

          <div
            v-if="loanedCopies.length === 0"
            class="mt-3 rounded border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600"
          >
            Nessuna copia attualmente in prestito.
          </div>

          <div v-else class="mt-4 space-y-3">
            <div
              v-for="copy in loanedCopies"
              :key="copy.id"
              class="overflow-hidden rounded border border-slate-300 bg-slate-50"
            >
              <div class="flex items-center justify-between gap-3 px-4 py-3">
                <span class="font-medium text-slate-800">{{ copy.barcode }}</span>
                <button
                  type="button"
                  class="bg-slate-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800"
                  @click="toggleCopyDetails(copy.id)"
                >
                  {{ isCopyExpanded(copy.id) ? 'Nascondi' : 'Mostra' }} membri
                </button>
              </div>

              <div v-if="isCopyExpanded(copy.id)" class="border-t border-slate-300 bg-white p-3">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                  <thead
                    class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"
                  >
                    <tr>
                      <th class="px-4 py-3">Membro</th>
                      <th class="px-4 py-3 w-80">Email</th>
                      <th class="px-4 py-3">In prestito dal</th>
                      <th class="px-4 py-3">va restituito entro</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-slate-100">
                    <tr v-for="loan in copy.loans" :key="loan.id">
                      <td class="px-4 py-3 text-slate-600">
                        {{ loan.user?.name ?? 'Membro non disponibile' }}
                      </td>
                      <td class="px-4 py-3 font-medium text-slate-900">
                        {{ loan.user?.email ?? 'Membro non disponibile' }}
                      </td>
                      <td class="px-4 py-3 text-slate-600">
                        {{ formatLoanDate(loan.loaned_at) }}
                      </td>
                      <td class="px-4 py-3 text-slate-600">
                        {{ formatLoanDate(loan.due_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>