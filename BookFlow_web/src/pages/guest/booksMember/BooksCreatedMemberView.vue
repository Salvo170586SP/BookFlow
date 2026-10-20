<script setup lang="ts">
import { onMounted, ref } from 'vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import type { PaginationMeta } from '@/types/member'
import { fetchBookCreated } from '@/api/books'
import type { Book } from '@/types/book'

const books = ref<Book[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

async function loadMyBook(page = 1) {
  loading.value = true
  error.value = null

  try {
    const result = await fetchBookCreated()
    books.value = result.data
    meta.value = result.meta
  } catch {
    error.value = 'Impossibile caricare i tuoi prestiti. Riprova.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void loadMyBook()
})
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8] text-[#242820]">
    <main>
      <section class="mx-auto max-w-6xl px-5 pb-12 pt-10 sm:px-8 sm:pb-16">
        <div class="mb-10">
          <p
            class="mb-4 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
          >
            <span class="size-2 bg-[#d45b3e]"></span>
            Le tue donazioni
          </p>
          <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <h1 class="text-5xl font-black leading-[0.98] tracking-[-0.06em] sm:text-6xl">
                I tuoi libri donati<span class="font-serif font-normal italic text-[#d45b3e]"
                  >.</span
                >
              </h1>
            </div>
            <RouterLink
              to="/dashboard"
              class="w-fit border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
            >
              Torna alla dashboard
            </RouterLink>
          </div>
        </div>

        <p
          v-if="error"
          role="alert"
          class="mt-6 border border-[#d45b3e]/40 bg-[#d45b3e]/5 p-5 font-medium text-[#8d3827]"
        >
          {{ error }}
        </p>

        <LoaderComponent v-if="loading && books.length === 0" />

        <section v-else aria-label="Libri in catalogo" class="mt-8">
          <div class="mb-4 flex items-center justify-between gap-4">
            <h2 class="text-xl font-black tracking-tight sm:text-2xl">Storie da raccontare</h2>
            <p v-if="meta" class="text-xs font-semibold text-[#65675e]">
              {{ meta.total }} {{ meta.total === 1 ? 'libro' : 'libri' }}
            </p>
          </div>

          <div
            v-if="books.length > 0"
            class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            :class="{ 'opacity-60': loading }"
          >
            <article
              v-for="(book, index) in books"
              :key="book.id"
              class="flex min-h-70 flex-col border border-[#242820]/25 bg-[#ede7da] p-4"
            >
              <div class="flex items-start gap-4">
                <div
                  role="img"
                  :aria-label="`Segnaposto copertina per ${book.title}`"
                  class="flex aspect-[3/4] w-24 shrink-0 flex-col items-center justify-center gap-3 border border-[#242820]/20 p-2 text-center text-[#f5f1e8]"
                  :class="[
                    ['bg-[#64704a]', 'bg-[#d45b3e]', 'bg-[#d5ad56]'][index % 3],
                    index % 3 === 2 ? 'text-[#242820]' : '',
                  ]"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="size-8" aria-hidden="true">
                    <path
                      d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                      stroke="currentColor"
                      stroke-width="1.4"
                    />
                  </svg>
                  <span class="text-[9px] font-bold uppercase leading-tight tracking-[0.12em]"
                    >Copertina<br />in arrivo</span
                  >
                </div>
                <div class="min-w-0 pt-1">
                  <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#64704a]">
                    {{ book.category.name }}
                  </p>
                  <h3 class="mt-2 font-serif text-xl font-bold leading-tight">
                    {{ book.title }}
                  </h3>
                  <p class="mt-2 text-sm leading-5 text-[#65675e]">
                    {{
                      book.authors.map((author) => author.name).join(', ') || 'Autore non indicato'
                    }}
                  </p>
                </div>
              </div>

              <div class="mt-5 border-t border-[#242820]/20 pt-4">
                <div class="flex flex-wrap justify-between gap-2 text-xs text-[#65675e]">
                  <span>{{ book.publisher?.name ?? 'Editore non indicato' }}</span>
                  <span>{{ book.published_year ?? 'Anno non indicato' }}</span>
                </div>
              </div>

              <RouterLink
                :to="`/books/${book.id}/details`"
                class="mt-auto flex items-center justify-between border border-[#d45b3e]/40 bg-[#d45b3e]/10 px-4 py-2.5 text-sm font-bold transition-colors hover:bg-[#d45b3e]/20"
              >
                Scopri il libro
                <span aria-hidden="true">→</span>
              </RouterLink>
            </article>
          </div>
          <div
            v-else-if="!loading && !error"
            class="mt-4 border border-[#242820]/25 bg-[#ede7da] p-8 text-center"
          >
            <p class="font-serif text-2xl italic text-[#65675e]">Nessun libro trovato.</p>
            <p class="mt-2 text-sm text-[#65675e]">Prova a modificare i filtri di ricerca.</p>
          </div>
        </section>
      </section>
    </main>
  </div>
</template>
 