<script setup lang="ts">
import { createMemberLoan, fetchMembers } from '@/api/members'
import { fetchAvailableCopies, fetchBook } from '@/api/books'
import LoaderComponent from '@/components/LoaderComponent.vue'
import type { Book, BookCopy, PaginationMeta } from '@/types/book'
import type { Member } from '@/types/member'
import { computed, watch, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const members = ref<Member[]>([])
const meta = ref<PaginationMeta | null>(null)
const book = ref<Book | null>(null)
const copies = ref<BookCopy[]>([])
const selectedMember = ref<Member | null>(null)
const selectedCopyId = ref<number | null>(null)
const dueAt = ref(defaultDueDate())

const loading = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const page = computed(() => Math.max(Number(route.query.page) || 1, 1))
const bookId = computed(() => Number(route.params.id))

let lastRequest = 0

function defaultDueDate(): string {
  const date = new Date()
  date.setDate(date.getDate() + 14)
  return formatLocalDate(date)
}

function formatLocalDate(date: Date): string {
  const offset = date.getTimezoneOffset()
  return new Date(date.getTime() - offset * 60_000).toISOString().slice(0, 10)
}

async function load(): Promise<void> {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null
  success.value = null

  if (!Number.isInteger(bookId.value) || bookId.value < 1) {
    error.value = 'ID libro non valido.'
    loading.value = false
    return
  }

  try {
    const [result, selectedBook, availableCopies] = await Promise.all([
      fetchMembers(page.value),
      fetchBook(bookId.value),
      fetchAvailableCopies(bookId.value),
    ])

    if (requestId !== lastRequest) return

    if (result.meta.last_page > 0 && page.value > result.meta.last_page) {
      router.replace({ query: { ...route.query, page: result.meta.last_page } })
      return
    }

    members.value = result.data
    meta.value = result.meta
    book.value = selectedBook
    copies.value = availableCopies
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare libro, copie disponibili o membri. Riprova.'
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

function selectMember(member: Member): void {
  if (selectedMember.value?.id === member.id) {
    cancelLoan()
    return
  }

  selectedMember.value = member
  selectedCopyId.value = null
  dueAt.value = defaultDueDate()
  error.value = null
  success.value = null
}

function cancelLoan(): void {
  selectedMember.value = null
  selectedCopyId.value = null
  error.value = null
  success.value = null
}

async function addLoan(): Promise<void> {
  const member = selectedMember.value
  const copyId = selectedCopyId.value
  if (!member || copyId === null) return

  submitting.value = true
  error.value = null

  try {
    await createMemberLoan(member.id, { copy_id: copyId, due_at: dueAt.value })
    copies.value = copies.value.filter((copy) => copy.id !== copyId)
    member.loans_count += 1
    selectedMember.value = null
    selectedCopyId.value = null
    router.push(`/admin/members/${member.id}/details`)
    success.value = 'Prestito creato correttamente.'
  } catch {
    error.value = 'Impossibile creare il prestito. Verifica la disponibilità della copia e riprova.'
  } finally {
    submitting.value = false
  }
}

function formatMemberDate(date: string): string {
  return new Intl.DateTimeFormat('it-IT', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

watch([page, bookId], load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">

      <div class="space-y-3">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Presta libro</h1>
        <RouterLink to="/admin/books" class="font-semibold border border-gray-300 px-3 hover:bg-gray-200">Indietro</RouterLink>
      </div>

      <p v-if="book" class="mt-2 text-slate-600">
        Libro selezionato: <span class="font-semibold">{{ book.title }}</span>
      </p>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>
      <p v-if="success" class="mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ success }}
      </p>
      <p
        v-if="!loading && book && copies.length === 0"
        class="mt-6 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800"
      >
        Al momento non ci sono copie disponibili per questo libro.
      </p>

      <LoaderComponent v-if="loading" />

      <section v-else>
        <div
          class="mt-6 overflow-x-auto border border-slate-300 bg-white transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3 text-start">Seleziona</th>
                <th class="px-4 py-3 w-80">Nome</th>
                <th class="px-4 py-3 w-80">Email</th>
                <th class="px-4 py-3 w-80">telefono</th>
                <th class="px-4 py-3 w-80">Indirizzo</th>
                <th class="px-4 py-3 w-80">Città di Nascita</th>
                <th class="px-4 py-3 w-80">iscritto dal</th>
                <th class="px-4 py-3 w-80 text-center">Libri in prestito</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="member in members" :key="member.id">
                <td class="px-4 py-3 text-slate-600">
                  <div class="text-center">
                    <button
                      :disabled="copies.length === 0"
                      :class="
                        selectedMember?.id === member.id
                          ? 'bg-gray-500 hover:bg-gray-600'
                          : 'bg-gray-100 hover:bg-gray-200'
                      "
                      class="text-white cursor-pointer w-5 h-5 rounded-full border border-black disabled:cursor-not-allowed disabled:opacity-50"
                      @click="selectMember(member)"
                    ></button>
                  </div>
                </td>
                <td class="px-4 py-3 font-medium text-slate-900 capitalize">{{ member.name }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ member.email }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.phone || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.address || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.birth_city || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ formatMemberDate(member.created_at) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="border border-green-500 text-green-700 rounded-full inline-block w-6 h-6 bg-green-200 text-center"
                  >
                    {{ member.loans_count }}
                  </span>
                </td>
              </tr>

              <tr v-if="members.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                  Nessun membro trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="meta" class="mt-4 flex items-center justify-between text-sm text-slate-600">
          <p>Pagina {{ meta.current_page }} di {{ meta.last_page }} · {{ meta.total }} membri</p>

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

        <form
          v-if="selectedMember"
          class="mt-6 border border-slate-300 bg-white p-5"
          @submit.prevent="addLoan"
        >
          <h2 class="font-semibold text-slate-900">
            Assegna una copia a <span class="capitalize">{{ selectedMember?.name }}</span>
          </h2>
          <p class="text-sm font-semibold">*Selezionare prima un membro</p>
          <div class="mt-4 flex flex-wrap items-end gap-4">
            <label class="flex min-w-64 flex-col gap-1 text-sm text-slate-700">
              Copia disponibile
              <select
                v-model.number="selectedCopyId"
                required
                class="border border-slate-300 bg-white px-3 py-2"
                :disabled="copies.length === 0 || submitting"
              >
                <option :value="null" disabled>Seleziona una copia</option>
                <option v-for="copy in copies" :key="copy.id" :value="copy.id">
                  {{ copy.barcode }}
                </option>
              </select>
            </label>

            <label class="flex flex-col gap-1 text-sm text-slate-700">
              Restituzione entro
              <input
                v-model="dueAt"
                required
                type="date"
                :min="formatLocalDate(new Date())"
                class="border border-slate-300 bg-white px-3 py-2"
                :disabled="submitting"
              />
            </label>

            <button
              type="submit"
              :disabled="submitting || selectedCopyId === null || copies.length === 0"
              class="border border-green-700 bg-green-900 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ submitting ? 'Salvataggio…' : 'Conferma prestito' }}
            </button>
            <button
              type="button"
              :disabled="submitting"
              class="border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-100"
              @click="cancelLoan"
            >
              Annulla
            </button>
          </div>
          <p v-if="copies.length === 0" class="mt-3 text-sm text-amber-700">
            Non ci sono copie disponibili per questo libro.
          </p>
        </form>
      </section>
    </main>
  </div>
</template>