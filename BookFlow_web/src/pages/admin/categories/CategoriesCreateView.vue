<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { storeCategories } from './../../../api/categories'
 
const router = useRouter()
const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const form = ref({
  name: ''
})

async function submit() {
  loading.value = true
  errors.value = {}

  try {
    await storeCategories(form.value)
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
      <div class="flex items-center justify-between mb-20">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Crea categoria</h1>
        <RouterLink
          to="/admin/categories"
          class="border border-[#242820]/25 px-3 py-1 font-semibold transition hover:bg-[#ede7da]"
          >Indietro</RouterLink
        >
      </div>
      <form @submit.prevent="submit" class="w-100 p-5 border border-slate-300 bg-white mx-auto">
        <label for="name" class="flex flex-col font-semibold space-y-2">
          <span>Nome*</span>
          <input
            type="text"
            id="name"
            v-model="form.name"
            required
            class="bg-gray-100 border border-gray-300 px-2"
          />
        </label>
        <span v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</span>

        <div class="mt-5 text-end">
          <button
            class="cursor-pointer border border-[#64704a] bg-[#64704a] px-3 py-1 font-medium text-[#f5f1e8] transition hover:bg-[#4f5b39] disabled:cursor-not-allowed disabled:opacity-50"
          >
            Crea
          </button>
        </div>
      </form>
    </main>
  </div>
</template>