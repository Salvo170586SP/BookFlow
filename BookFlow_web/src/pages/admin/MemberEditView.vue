<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { fetchMember, updateMember } from '../../api/members'

const router = useRouter()
const route = useRoute()

const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const form = ref({ name: '' })

onMounted(async () => {
  const id = Number(route.params.id)
  const currentMember = await fetchMember(id)
  form.value.name = currentMember.name
})

async function submit(event: Event) {
  event.preventDefault()

  loading.value = true
  errors.value = {}

  try {
    const id = Number(route.params.id)
    await updateMember(form.value, id)
    router.push('/admin/members')
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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Modifica membro</h1>
        <RouterLink to="/admin/members">Indietro</RouterLink>
      </div>
      <form @submit.prevent="submit" class="w-100 p-5 bg-white mx-auto border border-slate-400">
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
            class="bg-blue-700 hover:bg-blue-800 cursor-pointer border border-black px-3 py-1 text-white font-medium"
          >
            Modifica
          </button>
        </div>
      </form>
    </main>
  </div>
</template>