<script setup lang="ts">
import { isAxiosError } from 'axios'
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue'
import { destroyBookCopy, fetchBook, fetchBookCopies, updateBookCopyStatus } from '@/api/books'
import type { Book, BookCopy, CopyStatus } from '@/types/book'

const route = useRoute()
const bookId = computed(() => Number(route.params.id))
const book = ref<Book | null>(null)
const copies = ref<BookCopy[]>([])
const draftStatuses = ref<Record<number, CopyStatus>>({})
const barcodeSearch = ref('')
const statusFilter = ref<CopyStatus | ''>('')
const loading = ref(false)
const updatingCopyId = ref<number | null>(null)
const deleting = ref(false)
const copyToDelete = ref<BookCopy | null>(null)
const showDeleteModal = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const statuses: { value: CopyStatus; label: string }[] = [
  { value: 'available', label: 'Disponibile' },
  { value: 'damaged', label: 'Danneggiata' },
  { value: 'lost', label: 'Smarrita' },
  { value: 'maintenance', label: 'In manutenzione' },
]

const filteredCopies = computed(() => {
  const search = barcodeSearch.value.trim().toLocaleLowerCase()

  return copies.value.filter((copy) => {
    const matchesBarcode = copy.barcode.toLocaleLowerCase().includes(search)
    const matchesStatus = statusFilter.value === '' || copy.status === statusFilter.value

    return matchesBarcode && matchesStatus
  })
})

function getErrorMessage(cause: unknown, fallback: string): string {
  if (isAxiosError<{ message?: string }>(cause)) {
    return cause.response?.data?.message ?? fallback
  }

  return fallback
}

async function load() {
  loading.value = true
  error.value = null

  try {
    const [loadedBook, loadedCopies] = await Promise.all([
      fetchBook(bookId.value),
      fetchBookCopies(bookId.value),
    ])
    book.value = loadedBook
    copies.value = loadedCopies
    draftStatuses.value = Object.fromEntries(
      loadedCopies.map((copy) => [copy.id, copy.status]),
    )
  } catch (cause) {
    error.value = getErrorMessage(cause, 'Impossibile caricare il libro e le sue copie.')
  } finally {
    loading.value = false
  }
}

async function saveStatus(copy: BookCopy) {
  if (updatingCopyId.value !== null || deleting.value) return

  const status = draftStatuses.value[copy.id]
  if (!status) {
    error.value = 'Seleziona uno stato valido per la copia.'
    return
  }

  updatingCopyId.value = copy.id
  error.value = null
  success.value = null

  try {
    const updatedCopy = await updateBookCopyStatus(
      bookId.value,
      copy.id,
      status,
    )
    copy.status = updatedCopy.status
    success.value = `Stato della copia ${copy.barcode} aggiornato.`
  } catch (cause) {
    error.value = getErrorMessage(cause, 'Impossibile aggiornare lo stato della copia.')
  } finally {
    updatingCopyId.value = null
  }
}

function askDelete(copy: BookCopy) {
  if (updatingCopyId.value !== null || deleting.value) return

  copyToDelete.value = copy
  showDeleteModal.value = true
  error.value = null
  success.value = null
}

async function deleteCopy() {
  if (!copyToDelete.value || deleting.value || updatingCopyId.value !== null) return

  deleting.value = true
  error.value = null

  try {
    await destroyBookCopy(bookId.value, copyToDelete.value.id)
    copies.value = copies.value.filter((copy) => copy.id !== copyToDelete.value?.id)
    delete draftStatuses.value[copyToDelete.value.id]
    success.value = `Copia ${copyToDelete.value.barcode} eliminata.`
    showDeleteModal.value = false
    copyToDelete.value = null
  } catch (cause) {
    error.value = getErrorMessage(cause, 'Impossibile eliminare la copia.')
  } finally {
    deleting.value = false
  }
}

watch(bookId, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8]">
    <main class="p-10">
      <div class="space-y-5">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
          Modifica copie di <span v-if="book">– {{ book.title }}</span>
        </h1>
        <RouterLink
          to="/admin/books"
          class="w-32 border border-[#242820]/25 bg-[#fffdf8] px-3 text-center font-semibold text-[#242820] transition hover:bg-[#ede7da]"
        >
          Indietro
        </RouterLink>
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>
      <p
        v-if="success"
        class="mt-6 rounded-lg border border-[#64704a]/25 bg-[#e8dfcd] px-4 py-3 text-sm text-[#4f5b39]"
      >
        {{ success }}
      </p>

      <p v-if="loading" class="mt-6 text-[#55594e]">Caricamento copie…</p>

      <section v-else-if="book" class="mt-6">
        <h2 class="mb-3 text-lg font-semibold text-slate-900">
          Copie mostrate: {{ filteredCopies.length }} di {{ copies.length }}
        </h2>

        <div class="mb-4 flex flex-wrap gap-4">
          <label class="flex min-w-64 flex-col gap-1 text-sm text-slate-700">
            Cerca per barcode
            <input
              v-model="barcodeSearch"
              type="search"
              placeholder="Inserisci il barcode"
              class="border border-slate-300 bg-white px-3 py-2"
            />
          </label>

          <label class="flex min-w-56 flex-col gap-1 text-sm text-slate-700">
            Filtra per stato
            <select
              v-model="statusFilter"
              class="border border-slate-300 bg-white px-3 py-2"
            >
              <option value="">Tutti gli stati</option>
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </label>
        </div>

        <div class="overflow-x-auto border border-slate-300 bg-white">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide">
              <tr>
                <th class="px-4 py-3">Barcode</th>
                <th class="px-4 py-3">Stato</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="copy in filteredCopies" :key="copy.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ copy.barcode }}</td>
                <td class="px-4 py-3">
                  <select
                    v-model="draftStatuses[copy.id]"
                    class="border border-slate-300 bg-white px-3 py-2"
                    :disabled="updatingCopyId !== null || deleting"
                    :aria-label="`Stato della copia ${copy.barcode}`"
                  >
                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                      {{ status.label }}
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <button
                      type="button"
                      class="border border-[#64704a] px-3 py-1 text-[#4f5b39] transition hover:bg-[#ede7da] disabled:cursor-not-allowed disabled:opacity-50"
                      :disabled="
                        updatingCopyId !== null || draftStatuses[copy.id] === copy.status
                      "
                      @click="saveStatus(copy)"
                    >
                      {{ updatingCopyId === copy.id ? 'Salvataggio…' : 'Salva stato' }}
                    </button>
                    <button
                      type="button"
                      class="border border-[#8d3827] px-3 py-1 text-[#8d3827] transition hover:bg-[#f5e4de] disabled:cursor-not-allowed disabled:opacity-50"
                      :disabled="deleting || updatingCopyId !== null"
                      @click="askDelete(copy)"
                    >
                      Elimina copia
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="copies.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Questo libro non ha copie.
                </td>
              </tr>
              <tr v-else-if="filteredCopies.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Nessuna copia corrisponde ai filtri selezionati.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <ConfirmDeleteModal
        v-model="showDeleteModal"
        :loading="deleting"
        @confirm="deleteCopy"
      >
        Vuoi davvero eliminare la copia
        <strong>{{ copyToDelete?.barcode }}</strong>?
      </ConfirmDeleteModal>
    </main>
  </div>
</template>
