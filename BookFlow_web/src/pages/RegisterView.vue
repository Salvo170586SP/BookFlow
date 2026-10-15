 <script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from './../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const errors = ref<Record<string, string[]>>({})
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}

  try {
    await auth.register(form.value)
    router.push('/dashboard')
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
  <div class="min-h-screen bg-[#f5f1e8] text-[#242820]">
    <header class="border-b border-[#242820]/20">
      <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
        <RouterLink to="/" class="flex items-center gap-3" aria-label="BookFlow, home">
          <span class="grid size-10 place-items-center bg-[#d45b3e] text-[#f5f1e8]">
            <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
              <path
                d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linecap="square"
              />
            </svg>
          </span>
          <span class="text-xl font-black tracking-tight"
            >Bookflow</span
          >
        </RouterLink>
        <RouterLink
          to="/login"
          class="text-sm font-semibold transition-colors hover:text-[#d45b3e]"
        >
          Hai già un account? Accedi
        </RouterLink>
      </nav>
    </header>

    <main
      class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-10 sm:px-8 sm:py-16 lg:grid-cols-[0.85fr_1fr] lg:gap-20"
    >
      <section>
        <p
          class="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-[#64704a]"
        >
          <span class="size-2 bg-[#d45b3e]"></span>
          Una community di lettori
        </p>
        <h1 class="max-w-xl text-5xl font-black leading-[0.98] tracking-[-0.06em] sm:text-6xl">
          Le storie migliori
          <span class="font-serif font-normal italic text-[#d45b3e]">sono quelle condivise.</span>
        </h1>
        <p class="mt-6 max-w-lg text-lg leading-8 text-[#55594e]">
          Diventa socio di BookFlow: potrai prendere in prestito libri e scambiarli con gli altri
          membri della biblioteca sociale.
        </p>
        <div class="mt-10 border-t border-[#242820]/25 pt-5">
          <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#74766d]">
            La tua prossima lettura comincia qui
          </p>
          <div class="mt-5 flex items-end gap-2" aria-hidden="true">
            <span class="h-16 w-7 border border-[#242820] bg-[#64704a]"></span>
            <span class="h-20 w-8 border border-[#242820] bg-[#d45b3e]"></span>
            <span class="h-14 w-7 border border-[#242820] bg-[#d5ad56]"></span>
            <span class="ml-2 font-serif text-xl italic text-[#64704a]">Prendi. Leggi. Passa.</span>
          </div>
        </div>
      </section>

      <section class="border border-[#242820] bg-[#ede7da] p-6 sm:p-9">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#d45b3e]">Il primo passo</p>
        <h2 class="mt-3 text-2xl font-black tracking-tight">Registrati come socio</h2>
        <p class="mt-2 text-sm leading-6 text-[#65675e]">
          Crea il tuo account e inizia a condividere nuove storie.
        </p>

        <form class="mt-7 space-y-4" @submit.prevent="submit">
          <div>
            <label for="name" class="block text-sm font-semibold text-[#242820]">Nome</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              autocomplete="name"
              class="mt-2 block w-full border border-[#242820]/35 bg-[#f5f1e8] px-3 py-3 text-[#242820] outline-none transition-colors placeholder:text-[#74766d] focus:border-[#d45b3e] focus:ring-1 focus:ring-[#d45b3e]"
            />
            <p v-if="errors.name" class="mt-2 text-sm text-red-700">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-semibold text-[#242820]">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="mt-2 block w-full border border-[#242820]/35 bg-[#f5f1e8] px-3 py-3 text-[#242820] outline-none transition-colors placeholder:text-[#74766d] focus:border-[#d45b3e] focus:ring-1 focus:ring-[#d45b3e]"
            />
            <p v-if="errors.email" class="mt-2 text-sm text-red-700">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-[#242820]"
              >Password</label
            >
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              class="mt-2 block w-full border border-[#242820]/35 bg-[#f5f1e8] px-3 py-3 text-[#242820] outline-none transition-colors placeholder:text-[#74766d] focus:border-[#d45b3e] focus:ring-1 focus:ring-[#d45b3e]"
            />
            <p v-if="errors.password" class="mt-2 text-sm text-red-700">{{ errors.password[0] }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-[#242820]">
              Conferma password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="mt-2 block w-full border border-[#242820]/35 bg-[#f5f1e8] px-3 py-3 text-[#242820] outline-none transition-colors placeholder:text-[#74766d] focus:border-[#d45b3e] focus:ring-1 focus:ring-[#d45b3e]"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full cursor-pointer border border-[#d45b3e] bg-[#d45b3e] px-5 py-3.5 text-sm font-bold text-white transition-colors hover:border-[#242820] hover:bg-[#242820] focus:outline-none focus:ring-2 focus:ring-[#d45b3e] focus:ring-offset-2 focus:ring-offset-[#ede7da] disabled:cursor-not-allowed disabled:opacity-60"
          >
            {{ loading ? 'Registrazione in corso...' : 'Crea il tuo account' }}
          </button>
        </form>

        <p class="mt-6 border-t border-[#242820]/20 pt-5 text-sm text-[#65675e]">
          Hai già un account?
          <RouterLink
            to="/login"
            class="ml-1 font-bold text-[#d45b3e] underline decoration-1 underline-offset-4 hover:text-[#242820]"
          >
            Accedi
          </RouterLink>
        </p>
      </section>
    </main>

    <footer class="border-t border-[#242820]/20">
      <div
        class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 text-xs text-[#65675e] sm:px-8"
      >
        <RouterLink to="/" class="font-semibold hover:text-[#d45b3e]">← Torna alla home</RouterLink>
        <span class="hidden sm:block">Una biblioteca sociale, fatta per essere condivisa.</span>
      </div>
    </footer>
  </div>
</template>