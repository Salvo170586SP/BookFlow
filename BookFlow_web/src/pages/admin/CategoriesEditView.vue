<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { fetchCategory, updateCategories } from './../../api/categories'
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
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <div class="flex items-center justify-between mb-20">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Modifica categoria</h1>
        <RouterLink to="/admin/categories" class="font-semibold">Indietro</RouterLink>
      </div>

      <LoaderComponent v-if="loading" />

      <form
        v-else
        @submit.prevent="submit"
        class="w-100 border border-slate-400 p-5 bg-white mx-auto"
      >
        <label for="name" class="flex flex-col font-semibold space-y-2">
          <span>Nome*</span>
          <input
            type="text"
            id="name"
            v-model="form.name"
            class="bg-gray-100 border border-gray-400 px-2"
          />
        </label>
        <span v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</span>

        <div class="mt-5 text-end">
          <button
            class="bg-green-700 hover:bg-green-800 cursor-pointer px-3 py-1 text-white font-medium border border-black"
          >
            Crea
          </button>
        </div>
      </form>
    </main>
  </div>
</template>