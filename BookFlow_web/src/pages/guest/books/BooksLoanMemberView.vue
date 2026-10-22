<script setup lang="ts">
import { createMemberLoan } from '@/api/members'
import { fetchAvailableCopies, fetchBook } from '@/api/books'
import LoaderComponent from '@/components/LoaderComponent.vue'
import type { Book, BookCopy } from '@/types/book'
import { computed, watch, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ReviewModal from '@/components/ReviewModal.vue'

const route = useRoute()
const auth = useAuthStore()
const book = ref<Book | null>(null)
const copies = ref<BookCopy[]>([])
const dueAt = ref(defaultDueDate())

const loading = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)
const successModalOpen = ref(false)
const successConfirm = ref(false)
const reservation = ref<{ dueAt: string; barcode: string } | null>(null)

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

function formatDueDate(value: string): string {
  const [year, month, day] = value.slice(0, 10).split('-').map(Number)
  return new Intl.DateTimeFormat('it-IT').format(new Date(year, month - 1, day))
}

async function load(): Promise<void> {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null
  reservation.value = null
  successModalOpen.value = false

  if (!Number.isInteger(bookId.value) || bookId.value < 1) {
    error.value = 'ID libro non valido.'
    loading.value = false
    return
  }

  try {
    const [selectedBook, availableCopies] = await Promise.all([
      fetchBook(bookId.value),
      fetchAvailableCopies(bookId.value),
    ])

    if (requestId !== lastRequest) return

    book.value = selectedBook
    copies.value = availableCopies
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare il libro o le copie disponibili. Riprova.'
    }
  } finally {
    if (requestId === lastRequest) {
      loading.value = false
    }
  }
}

async function addLoan(): Promise<void> {
  const selectedBook = book.value
  const memberId = auth.user?.id
  if (!selectedBook || !memberId || copies.value.length === 0) return

  submitting.value = true
  error.value = null

  try {
    const loan = await createMemberLoan(memberId, {
      book_id: selectedBook.id,
      due_at: dueAt.value,
    })
    copies.value = copies.value.filter((copy) => copy.id !== loan.copy.id)
    selectedBook.available_copies_count = Math.max(selectedBook.available_copies_count - 1, 0)
    reservation.value = { dueAt: loan.due_at, barcode: loan.copy.barcode }
    successModalOpen.value = true
    successConfirm.value = true
  } catch {
    error.value =
      'Impossibile prenotare il libro: le copie potrebbero essere state assegnate nel frattempo. Riprova.'
    try {
      const [updatedBook, availableCopies] = await Promise.all([
        fetchBook(selectedBook.id),
        fetchAvailableCopies(selectedBook.id),
      ])
      book.value = updatedBook
      copies.value = availableCopies
    } catch {
      error.value = 'Prenotazione non riuscita e impossibile aggiornare la disponibilità. Riprova.'
    }
  } finally {
    submitting.value = false
  }
}

watch(bookId, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8] text-[#242820]">
    <main class="mx-auto max-w-6xl px-5 pb-12 pt-10 sm:px-8 sm:pb-16">
      <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <p
          class="flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
        >
          <span class="size-2 bg-[#d45b3e]"></span>
          Prenota una lettura
        </p>
        <RouterLink
          :to="book ? `/books/${book.id}/details` : '/books'"
          class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
        >
          ← Torna al libro
        </RouterLink>
      </div>

      <LoaderComponent v-if="loading" />

      <section v-else>
        <p
          v-if="error"
          role="alert"
          class="mb-6 border border-[#d45b3e]/40 bg-[#d45b3e]/5 p-5 font-medium text-[#8d3827]"
        >
          {{ error }}
        </p>

        <div v-if="book">
          <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64704a]">
            {{ book.category.name }}
          </p>
          <h1
            class="mt-3 max-w-4xl text-4xl font-black leading-[1.02] tracking-[-0.05em] sm:text-6xl"
          >
            Prenota
            <span class="font-serif font-normal italic text-[#d45b3e]">{{ book.title }}</span>
          </h1>
          <p class="mt-4 leading-7 text-[#65675e]">
            Ti assegneremo automaticamente una copia disponibile. Non serve scegliere il codice
            della copia.
          </p>

          <section
            v-if="!successConfirm"
            class="mt-8 border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-7"
          >
            <div class="mb-5 flex items-center justify-between border-b border-[#242820]/25 pb-4">
              <h2 class="text-xs font-bold uppercase tracking-[0.18em]">La tua prenotazione</h2>
              <span class="text-xs font-bold text-[#64704a]">BookFlow / Soci</span>
            </div>

            <p class="text-sm font-semibold text-[#55594e]">
              <span
                class="mr-1 inline-block size-2"
                :class="copies.length > 0 ? 'bg-[#64704a]' : 'bg-[#d45b3e]'"
              ></span>
              {{
                copies.length > 0
                  ? `${copies.length} ${
                      copies.length === 1 ? 'copia disponibile' : 'copie disponibili'
                    }`
                  : 'Al momento non ci sono copie disponibili.'
              }}
            </p>

            <form
              class="mt-6 flex flex-col items-start gap-5 sm:flex-row sm:items-end"
              @submit.prevent="addLoan"
            >
              <label class="flex w-full max-w-xs flex-col gap-2">
                <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]">
                  Restituzione entro
                </span>
                <input
                  v-model="dueAt"
                  required
                  type="date"
                  :min="formatLocalDate(new Date())"
                  class="w-full border border-[#242820]/25 bg-[#f5f1e8] px-3 py-2.5 outline-none focus:border-[#d45b3e]"
                  :disabled="submitting || copies.length === 0"
                />
              </label>

              <button
                type="submit"
                :disabled="submitting || copies.length === 0"
                class="border border-[#d45b3e]/40 bg-[#d45b3e]/15 px-5 py-2.5 text-sm font-bold transition-colors hover:bg-[#d45b3e]/25 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {{ submitting ? 'Prenotazione…' : 'Conferma prenotazione' }}
              </button>
            </form>
          </section>

          <section
            v-if="successConfirm"
            class="mt-8 border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-7"
          >
            Prenotazione confermata! Torna al
            <RouterLink
              to="/books"
              class="text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
            >
              Catalogo
            </RouterLink>
          </section>
        </div>
      </section>
      <ReviewModal v-model="successModalOpen">
        <p class="mb-5 font-medium text-sm text-center">
          IMPORTANTE: Ti invitiamo a stampare la riceviuta che ti abbiamo inviato al tuo indirizzo
          email e consegnarla al momento del ritiro
        </p>
        <div class="text-center">
          <p>
            La prenotazione di
            <span class="font-bold text-[#242820]">{{ book.title }}</span>
            è andata a buon fine.
          </p>
          <p class="mt-3">
            Dovrai restituire il libro entro il
            <span class="font-bold text-[#242820]">
              {{ reservation ? formatDueDate(reservation.dueAt) : '' }} </span
            >.
          </p>
          <p v-if="reservation" class="mt-3 text-sm">
            Copia assegnata: <span class="font-semibold">{{ reservation.barcode }}</span>
          </p>
        </div>
      </ReviewModal>
    </main>
  </div>
</template>
