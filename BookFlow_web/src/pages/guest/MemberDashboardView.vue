<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { fetchBooks } from '@/api/books'
import { useAuthStore } from '@/stores/auth'
import type { Book } from '@/types/book'

const auth = useAuthStore()
const books = ref<Book[]>([])
const totalBooks = ref<number | null>(null)
const booksLoading = ref(true)
const booksError = ref<string | null>(null)
const coverColors = ['bg-[#64704a]', 'bg-[#d45b3e]', 'bg-[#d5ad56]', 'bg-[#64704a]']

onMounted(async () => {
  try {
    const catalog = await fetchBooks()
    books.value = catalog.data.slice(0, 4)
    totalBooks.value = catalog.meta.total
  } catch {
    booksError.value = 'Il catalogo non è al momento disponibile. Riprova più tardi.'
  } finally {
    booksLoading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen bg-[#f5f1e8] text-[#242820]">
    <main>
      <section class="mx-auto max-w-6xl px-5 pb-14 pt-12 sm:px-8 sm:pb-10 sm:pt-10">
        <div class="space-y-10">
          <div>
            <p
              class="py-10 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
            >
              <span class="size-2 bg-[#d45b3e]"></span>
              La tua biblioteca sociale
            </p>
            <h1 class="max-w-2xl text-5xl font-black leading-[0.98] tracking-[-0.06em] sm:text-6xl">
              Ciao<span v-if="auth.user">, {{ auth.user.name }}</span
              ><span v-else>, socio</span>.
              <span class="font-serif font-normal italic text-[#d45b3e]"
                >Da quale storia parti?</span
              >
            </h1>
          </div>

          <div class="border border-[#242820]/25 bg-[#e8dfcd] p-5 sm:p-7 mb-10">
            <div
              class="flex items-center justify-between border-b border-[#242820]/25 pb-4 text-[10px] font-bold uppercase tracking-[0.18em]"
            >
              <span>La community, in breve</span>
              <span class="text-[#64704a]">BookFlow / Soci</span>
            </div>
            <div class="grid grid-cols-3 gap-3 py-6">
              <div class="border border-[#242820]/25 bg-[#f5f1e8] p-4">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]">
                  Libri in catalogo
                </p>
                <p class="mt-2 font-serif text-4xl italic text-[#d45b3e]">
                  <span v-if="totalBooks !== null">{{ totalBooks }}</span>
                  <span v-else-if="booksLoading" aria-label="Caricamento">…</span>
                  <span v-else>—</span>
                </p>
              </div>
              <div class="border border-[#242820]/25 bg-[#f5f1e8] p-4">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]">
                  Copie prese in prestito
                </p>
                <p class="mt-2 font-serif text-4xl italic text-[#d45b3e]">
                  {{ auth?.user?.loans.length }}
                </p>
              </div>
              <div class="border border-[#242820]/25 bg-[#f5f1e8] p-4">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#65675e]">
                  Il bello di BookFlow
                </p>
                <p class="mt-2 font-serif text-4xl italic text-[#d45b3e]">Leggere insieme</p>
              </div>
            </div>
            <p class="border-t border-[#242820]/25 pt-4 text-sm leading-6 text-[#55594e]">
              Ogni libro condiviso è un’occasione per incontrare una nuova storia.
            </p>
          </div>
        </div>
      </section>

      <section
        id="catalogo"
        aria-labelledby="catalogo-title"
        class="px-5 py-14 sm:px-8 sm:py-20 border-t border-[#242820]/20 bg-[#ede7da]"
      >
        <div class="mx-auto max-w-6xl">
          <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
              <h2 id="catalogo-title" class="text-3xl font-black tracking-tight sm:text-4xl">
                Inizia da una di queste storie.
              </h2>
            </div>

            <p class="max-w-sm font-medium leading-7 text-[#65675e]">
              Una selezione di storie dal catalogo della biblioteca.
            </p>
          </div>

          <p
            v-if="booksError"
            role="alert"
            class="border border-[#d45b3e]/40 bg-[#d45b3e]/5 p-5 font-medium text-[#8d3827]"
          >
            {{ booksError }}
          </p>
          <div
            v-else-if="booksLoading"
            role="status"
            class="border border-[#242820]/25 bg-[#ede7da] p-5 text-sm font-medium text-[#65675e]"
          >
            Caricamento del catalogo…
          </div>
          <div
            v-else-if="books.length === 0"
            class="border border-[#242820]/25 bg-[#ede7da] p-5 font-medium text-[#65675e]"
          >
            Non ci sono ancora libri da mostrare.
          </div>

          <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <RouterLink
              v-for="(book, index) in books"
              :key="book.id"
              :to="`/books/${book.id}/details`"
              class="flex min-h-72 flex-col border border-[#242820]/25 bg-[#f5f1e8] p-4 transition-colors hover:border-[#d45b3e]"
            >
              <div
                class="flex h-20 items-end border border-[#242820]/20 p-4 text-[#f5f1e8]"
                :class="coverColors[index % coverColors.length]"
              >
                <span class="max-w-48 font-serif text-lg leading-tight italic">
                  {{ book.title }}
                </span>
              </div>
              <p class="mt-4 text-xs font-bold uppercase tracking-[0.12em] text-[#64704a]">
                {{ book.category.name }}
              </p>
              <h3 class="mt-1 font-bold leading-snug">{{ book.title }}</h3>
              <p class="mt-1 text-sm text-[#65675e]">
                {{ book.authors.map((author) => author.name).join(', ') }}
              </p>
              <p class="mt-auto pt-4 text-xs font-semibold text-[#55594e]">
                <span
                  class="mr-1 inline-block size-2"
                  :class="book.available_copies_count > 0 ? 'bg-[#64704a]' : 'bg-[#d45b3e]'"
                ></span>
                {{
                  book.available_copies_count > 0
                    ? `${book.available_copies_count} copie ancora disponibili`
                    : 'Nessuna copia disponibile'
                }}
              </p>
            </RouterLink>
          </div>
          <div class="text-center mt-10">
            <RouterLink
              to="/books"
              class="border px-5 py-3 font-medium border-[#d45b3e]/40 bg-[#d45b3e]/10 hover:bg-[#d45b3e]/40"
              >Esplora tutto il catalogo</RouterLink
            >
          </div>
        </div>
      </section>
    </main>
    <footer class="border-t border-[#242820]/20">
      <div
        class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-6 text-center text-xs text-[#65675e] sm:flex-row sm:items-center sm:justify-between sm:px-8"
      >
        <span class="font-black tracking-tight text-[#242820]">Bookflow</span>
        <span>Una biblioteca sociale, fatta per essere condivisa.</span>
        <RouterLink to="/" class="font-semibold hover:text-[#d45b3e]">Torna alla home</RouterLink>
      </div>
    </footer>
  </div>
</template>
