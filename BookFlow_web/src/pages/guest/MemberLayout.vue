<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
const auth = useAuthStore()
const router = useRouter()
const isClick = ref(false)

function openMenu() {
  isClick.value = !isClick.value
}

async function logout() {
  await auth.logout()
  await router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f1e8] text-[#242820]">
    <header
      class="w-full fixed right-0 let-0 top-0 border-b border-[#242820]/20 bg-[#d45b3e]/15 backdrop-blur-md z-50"
    >
      <nav
        class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8"
      >
        <RouterLink to="/dashboard" class="flex items-center gap-3" aria-label="BookFlow, home">
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
            <RouterLink
              to="/books"
              class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
              >Catalogo</RouterLink
            >
            <RouterLink
              to="/loans-books"
              class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
            >
              Presi in prestito
            </RouterLink>

            <div class="group relative">
              <button
                class="border border-[#242820]/30 px-4 py-2 text-sm font-bold transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
              >
                Donazioni
              </button>
              <div class="hidden group-hover:flex flex-col gap-3 absolute bg-emerald-200 border p-3 w-50">
                <RouterLink
                  to=""
                  class="border border-[#242820]/30 px-4 py-2 text-sm font-bold bg-[#d45b3e]/5 transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
                >
                  Dona un libro
                </RouterLink>
                <RouterLink
                  to=""
                  class="border border-[#242820]/30 px-4 py-2 text-sm font-bold bg-[#d45b3e]/5 transition-colors hover:border-[#d45b3e] hover:text-[#d45b3e]"
                >
                  Fa una donazione
                </RouterLink>
              </div>
            </div>
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

    <main class="z-10 pt-20">
      <RouterView v-if="auth.hasRole('member')" />
    </main>
  </div>
</template>
