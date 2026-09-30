<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <span class="text-lg font-semibold text-slate-900">Dashboard</span>

        <button
          class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
          @click="logout"
        >
          Esci
        </button>
      </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">
        Benvenuto<span v-if="auth.user">, {{ auth.user?.name }}</span>
      </h1>
      <p class="mt-1 text-slate-600">Ecco il riepilogo del tuo account.</p>

      <div v-if="auth.user" class="mt-8 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-medium text-slate-500">Nome</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user?.name }}</p>
        </div>
   
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-medium text-slate-500">Email</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user?.email }}</p>
        </div>
      </div>
    </main>
  </div>
</template>