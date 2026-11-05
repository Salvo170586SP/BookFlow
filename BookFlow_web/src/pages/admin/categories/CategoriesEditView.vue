<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { fetchCategory, updateCategories } from './../../../api/categories'
import LoaderComponent from '@/components/LoaderComponent.vue'

const router = useRouter()
const route = useRoute()

const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const form = ref({ name: '' })
const formError = ref<string | null>(null)

onMounted(async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)
    const currentCategory = await fetchCategory(id)
    form.value.name = currentCategory.name
    loading.value = false
  } catch {
    formError.value = 'Impossibile caricare i dati del membro.'
  } finally {
    loading.value = false
  }
})

async function submit(event: Event) {
  event.preventDefault()

  loading.value = true
  errors.value = {}

  try {
    const id = Number(route.params.id)
    await updateCategories(form.value, id)
    router.push('/admin/categories')
  } catch (e) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      errors.value = e.response.data.errors
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8]">
    <main class="p-10">
      <div class="mb-20 flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Modifica categoria</h1>
        <RouterLink
          to="/admin/categories"
          class="border border-[#242820]/25 px-3 py-1 font-semibold transition hover:bg-[#ede7da]"
        >
          Indietro
        </RouterLink>
      </div>

      <LoaderComponent v-if="loading" admin-theme />

      <form
        v-else
        @submit.prevent="submit"
        class="mx-auto w-100 border border-[#242820]/20 bg-[#fffdf8] p-5"
      >
        <label for="name" class="flex flex-col font-semibold space-y-2">
          <span>Nome*</span>
          <input
            type="text"
            id="name"
            v-model="form.name"
            class="bg-gray-100 border border-gray-300 px-2"
          />
        </label>
        <span v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</span>

        <div class="mt-5 text-end">
          <button
            class="cursor-pointer border border-[#64704a] bg-[#64704a] px-3 py-1 font-medium text-[#f5f1e8] transition hover:bg-[#4f5b39] disabled:cursor-not-allowed disabled:opacity-50"
          >
            Modifica
          </button>
        </div>
      </form>
    </main>
  </div>
</template>