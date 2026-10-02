<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useCategoryStore } from '../../stores/category'

const category = useCategoryStore()
const router = useRouter()
const route = useRoute()

const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const form = ref({name: ''})

onMounted(async () => {
  const id = Number(route.params.id)

  const currentCategory = await category.fetchCategory(id)

  form.value.name = currentCategory.name
})

async function submit(event: Event) {
  event.preventDefault()

  loading.value = true
  errors.value = {}

  try {
    const id = Number(route.params.id)
 
    await category.update(form.value, id)

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
        <RouterLink
          to="/admin/categories"
          class="bg-black hover:bg-gray-700 text-white font-semibold px-3 py-1 rounded-lg"
          >Indietro</RouterLink
        >
      </div>
      <form @submit.prevent="submit" class="w-100 shadow p-5 rounded-lg bg-white mx-auto">
        <label for="name" class="flex flex-col font-semibold space-y-2">
          <span>Nome*</span>
          <input
            type="text"
            id="name"
            v-model="form.name"
            required
            class="bg-gray-100 rounded border border-gray-300 px-1"
          />
        </label>
        <span v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</span>

        <div class="mt-5 text-end">
          <button
            class="bg-green-700 hover:bg-green-800 cursor-pointer rounded-lg px-3 py-1 text-white font-medium"
          >
            Crea
          </button>
        </div>
      </form>
    </main>
  </div>
</template>