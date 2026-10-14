<script setup lang="ts">
import { fetchBook, storeBookCopies } from '../../../api/books'
import type { Book } from '../../../types/book'
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const book = ref<Book | null>(null)

const submitting = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)
const bookId = computed(() => Number(route.params.id))

const form = ref({ quantity: 1 })

async function addCopy() {
  if (submitting.value) return
  if (
    !Number.isInteger(form.value.quantity) ||
    form.value.quantity < 1 ||
    form.value.quantity > 1000
  ) {
    error.value = 'Inserisci un numero di copie compreso tra 1 e 1000.'
    return
  }

  submitting.value = true
  error.value = null
  success.value = null

  try {
    const createdCount = await storeBookCopies(bookId.value, form.value)
    success.value = `${createdCount} copie create. Barcode generati automaticamente; stato impostato su disponibile.`
    form.value.quantity = 1
  } catch {
    error.value = 'Impossibile creare le copie.'
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    book.value = await fetchBook(bookId.value)
  } catch {
    error.value = 'Impossibile caricare il libro.'
  }
})
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="p-10">
      <div class="space-y-5">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
          Aggiungi Copie a <span v-if="book"> – {{ book.title }}</span>
        </h1>
        <RouterLink
          :to="`/admin/books`"
          class="border border-gray-300 font-semibold w-32 text-center px-3 bg-gray-50 hover:bg-gray-200 text-black"
          >Indietro</RouterLink
        >
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>
      <p v-if="success" class="mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ success }}
      </p>

      <section>
        <form class="mt-6 border border-slate-300 bg-white p-5" @submit.prevent="addCopy">
          <div class="mt-4 flex flex-wrap items-end gap-4">
            <label class="flex min-w-64 flex-col gap-1 text-sm text-slate-700">
              Numero di copie
              <input
                v-model.number="form.quantity"
                type="number"
                min="1"
                max="1000"
                step="1"
                required
                class="border border-slate-300 bg-white px-3 py-2"
                :disabled="submitting"
              />
            </label>

            <button
              type="submit"
              :disabled="
                submitting ||
                !Number.isInteger(form.quantity) ||
                form.quantity < 1 ||
                form.quantity > 1000
              "
              class="border border-green-700 bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ submitting ? 'Caricamento…' : 'Crea copie' }}
            </button>
            <button
              type="button"
              class="border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-100"
            >
              Annulla
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>