<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchBook } from '../../../api/books'
import type { Book } from '../../../types/book'
import LoaderComponent from '../../../components/LoaderComponent.vue'
import { useAuthStore } from '../../../stores/auth'

const route = useRoute()
const auth = useAuthStore()

const loading = ref(false)
const error = ref<string | null>(null)

const book = ref<Book | null>(null)
const myActiveLoan = computed(() => {
  for (const copy of book.value?.copies ?? []) {
    const loan = copy.loans?.find(
      (loan) => loan.user_id === auth.user?.id && loan.returned_at == null
    )
    if (loan) return loan
  }

  return null
})
const isBookInMyPossession = computed(() => myActiveLoan.value !== null)
const formattedDueDate = computed(() =>
  myActiveLoan.value
    ? new Intl.DateTimeFormat('it-IT').format(new Date(myActiveLoan.value.due_at))
    : ''
)

let requestId = 0

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
  <div class="min-h-full bg-[#f5f1e8] text-[#242820]">
    <main class="mx-auto max-w-6xl px-5 pb-12 pt-10 sm:px-8 sm:pb-16">
      <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <p
          class="flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
        >
          <span class="size-2 bg-[#d45b3e]"></span>
          Scheda del libro
        </p>
        <RouterLink
          to="/books-created"
          class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
        >
          ← Torna ai tuoi libri
        </RouterLink>
      </div>

      <LoaderComponent v-if="loading" />

      <section v-else>
        <p
          v-if="error"
          role="alert"
          class="border border-[#d45b3e]/40 bg-[#d45b3e]/5 p-5 font-medium text-[#8d3827]"
        >
          {{ error }}
        </p>

        <div v-if="book">
          <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64704a]">
              {{ book.category.name }}
            </p>
            <h1
              class="mt-3 max-w-4xl text-4xl font-black leading-[1.02] tracking-[-0.05em] sm:text-6xl"
            >
              {{ book.title }}<span class="font-serif font-normal italic text-[#d45b3e]">.</span>
            </h1>
            <p class="mt-4 font-medium text-[#65675e]">
             <span class="font-medium italic">Autore:</span> {{ book.authors.map((author) => author.name).join(', ') || 'Autore non indicato' }}
            </p>
          </div>

          <div class="grid gap-5 lg:grid-cols-[minmax(15rem,0.8fr)_1.5fr]">
            <img
              v-if="book.cover"
              :src="book.cover"
              :alt="`Copertina di ${book.title}`"
              class="min-h-80 w-full border border-[#242820]/25 bg-[#64704a] object-contain sm:min-h-[28rem]"
            />
            <div
              v-else
              role="img"
              :aria-label="`Segnaposto della copertina di ${book.title}; immagine non disponibile`"
              class="flex min-h-80 flex-col items-center justify-center border border-[#242820]/25 bg-[#64704a] p-8 text-center text-[#f5f1e8] sm:min-h-[28rem]"
            >
              <svg viewBox="0 0 24 24" fill="none" class="mb-6 size-16" aria-hidden="true">
                <path
                  d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                  stroke="currentColor"
                  stroke-width="1.2"
                />
              </svg>
              <span class="font-serif text-2xl italic">{{ book.title }}</span>
              <span
                class="mt-5 border border-[#f5f1e8]/40 px-3 py-2 text-[10px] font-bold uppercase tracking-[0.16em]"
              >
                Copertina non disponibile
              </span>
            </div>

            <div class="flex flex-col gap-5">
              <section class="border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-7">
                <div
                  class="mb-5 flex items-center justify-between border-b border-[#242820]/25 pb-4"
                >
                  <h2 class="text-xs font-bold uppercase tracking-[0.18em]">Informazioni</h2>
                  <span class="text-xs font-bold text-[#64704a]">BookFlow / Soci</span>
                </div>
                <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#65675e]">
                      ISBN
                    </dt>
                    <dd class="mt-1 font-semibold">{{ book.isbn || 'Non indicato' }}</dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#65675e]">
                      Categoria
                    </dt>
                    <dd class="mt-1 font-semibold">{{ book.category.name || 'Non indicata' }}</dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#65675e]">
                      Editore
                    </dt>
                    <dd class="mt-1 font-semibold">
                      {{ book.publisher?.name ?? 'Non indicato' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#65675e]">
                      Anno di pubblicazione
                    </dt>
                    <dd class="mt-1 font-semibold">{{ book.published_year ?? 'Non indicato' }}</dd>
                  </div>
                </dl>
              </section>

              <section class="border border-[#242820]/25 bg-[#ede7da] p-5 sm:p-7">
                <h2 class="text-xs font-bold uppercase tracking-[0.18em]">Disponibilità</h2>
                <div class="mt-5 grid grid-cols-3 gap-3">
                  <div class="border border-[#242820]/20 bg-[#f5f1e8] p-3 sm:p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-[#65675e]">
                      Totali
                    </p>
                    <p class="mt-2 font-serif text-3xl italic text-[#d45b3e]">
                      {{ book.total_copies_count }}
                    </p>
                  </div>
                  <div class="border border-[#242820]/20 bg-[#f5f1e8] p-3 sm:p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-[#65675e]">
                      In prestito
                    </p>
                    <p class="mt-2 font-serif text-3xl italic text-[#d45b3e]">
                      {{ book.borrowed_copies_count }}
                    </p>
                  </div>
                  <div class="border border-[#242820]/20 bg-[#f5f1e8] p-3 sm:p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-[#65675e]">
                      Disponibili
                    </p>
                    <p class="mt-2 font-serif text-3xl italic text-[#d45b3e]">
                      {{ book.available_copies_count }}
                    </p>
                  </div>
                </div>
                <p class="mt-4 text-sm font-semibold text-[#55594e]">
                  <span
                    class="mr-1 inline-block size-2"
                    :class="book.available_copies_count > 0 ? 'bg-[#64704a]' : 'bg-[#d45b3e]'"
                  ></span>
                  {{
                    book.available_copies_count > 0
                      ? 'Questo libro è disponibile in biblioteca.'
                      : 'Al momento non ci sono copie disponibili.'
                  }}
                </p>
              </section>
            </div>
          </div>

          <section class="mt-5 border border-[#242820]/25 bg-[#ede7da] p-5 sm:p-7">
            <h2 class="text-xs font-bold uppercase tracking-[0.18em]">Descrizione</h2>
            <p class="mt-4 whitespace-pre-line leading-7 text-[#55594e]">
              {{
                book.description || 'Una descrizione non è ancora disponibile per questo titolo.'
              }}
            </p>
          </section>
        </div>
      </section>
    </main>
  </div>
</template>