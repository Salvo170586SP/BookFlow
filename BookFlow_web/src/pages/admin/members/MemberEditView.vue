<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { fetchMember, updateMember } from './../../../api/members'
import LoaderComponent from '@/components/LoaderComponent.vue'

const router = useRouter()
const route = useRoute()

const errors = ref<Record<string, string[]>>({})
const loading = ref(false)
const loadingMember = ref(true)
const loadFailed = ref(false)
const formError = ref<string | null>(null)
const form = ref({ name: '', phone: '', address: '', birth_city: '', birth_date: '' })

onMounted(async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)
    const currentMember = await fetchMember(id)
    form.value = {
      name: currentMember.name,
      phone: currentMember.detail?.phone || '',
      address: currentMember.detail?.address || '',
      birth_city: currentMember.detail?.birth_city || '',
      birth_date: currentMember.detail?.birth_date?.slice(0, 10) || '',
    }
    loading.value = false
  } catch {
    loadFailed.value = true
    formError.value = 'Impossibile caricare i dati del membro.'
  } finally {
    loadingMember.value = false
  }
})

async function submit() {
  loading.value = true
  errors.value = {}
  formError.value = null

  try {
    const id = Number(route.params.id)
    await updateMember(form.value, id)
    router.push('/admin/members')
  } catch (e) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      errors.value = e.response.data.errors
    } else {
      formError.value = 'Impossibile salvare le modifiche del membro.'
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
      <p v-if="formError" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ formError }}
      </p>

      <LoaderComponent v-if="loading" />

      <form
        v-else
        @submit.prevent="submit"
        class="w-100 p-5 bg-white mx-auto border border-slate-400"
      >
        <fieldset :disabled="loading || loadingMember || loadFailed" class="disabled:opacity-60">
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

          <label for="phone" class="flex flex-col font-semibold space-y-2">
            <span>Telefono</span>
            <input
              type="text"
              id="phone"
              v-model="form.phone"
              class="bg-gray-100 border border-gray-400 px-2"
            />
          </label>
          <span v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</span>

          <label for="address" class="flex flex-col font-semibold space-y-2">
            <span>Indirizzo</span>
            <input
              type="text"
              id="address"
              v-model="form.address"
              class="bg-gray-100 border border-gray-400 px-2"
            />
          </label>
          <span v-if="errors.address" class="mt-1 text-sm text-red-600">{{
            errors.address[0]
          }}</span>

          <label for="birth_city" class="flex flex-col font-semibold space-y-2">
            <span>Città di nascita</span>
            <input
              type="text"
              id="birth_city"
              v-model="form.birth_city"
              class="bg-gray-100 border border-gray-400 px-2"
            />
          </label>
          <span v-if="errors.birth_city" class="mt-1 text-sm text-red-600">{{
            errors.birth_city[0]
          }}</span>

          <label for="birth_date" class="flex flex-col font-semibold space-y-2">
            <span>Data di nascita</span>
            <input
              type="date"
              id="birth_date"
              v-model="form.birth_date"
              class="bg-gray-100 border border-gray-400 px-2"
            />
          </label>
          <span v-if="errors.birth_date" class="mt-1 text-sm text-red-600">{{
            errors.birth_date[0]
          }}</span>

          <div class="mt-5 text-end">
            <button
              class="bg-blue-700 hover:bg-blue-800 cursor-pointer border border-black px-3 py-1 text-white font-medium"
            >
              {{
                loadingMember
                  ? 'Caricamento...'
                  : loadFailed
                  ? 'Impossibile caricare'
                  : loading
                  ? 'Salvataggio...'
                  : 'Modifica'
              }}
            </button>
          </div>
        </fieldset>
      </form>
    </main>
  </div>
</template>
