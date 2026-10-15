<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { RouterLink, RouterView, useRouter } from 'vue-router'
const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f1e8] text-[#242820]">
    <header class="border-b border-[#242820]/20">
      <nav
        class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8"
      >
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
          <span class="text-xl font-black tracking-tight">Bookflow</span>
        </RouterLink>

        <div class="flex items-center justify-center gap-4 sm:gap-7">
          <nav
            aria-label="Navigazione dashboard"
            class="flex items-center justify-between gap-6 text-sm font-semibold"
          >
            <a href="#catalogo" class="transition-colors hover:text-[#d45b3e]">Catalogo</a>
            <a href="#prendi-in-prestito" class="transition-colors hover:text-[#d45b3e]">
              Prendi in prestito
            </a>
            <a href="#prendi-in-prestito" class="transition-colors hover:text-[#d45b3e]">
              I tuoi libri
            </a>
            <a href="#presta-un-libro" class="transition-colors hover:text-[#d45b3e]">
              Condividi un libro
            </a>
          </nav>
        </div>
        <div>
          <button
            class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
            @click="logout"
          >
            Esci
          </button>
        </div>
      </nav>
    </header>

    <main>
      <RouterView v-if="auth.hasRole('member')" />
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
