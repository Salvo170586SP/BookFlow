<script setup lang="ts">
import { useAuthStore } from './../stores/auth'

const auth = useAuthStore()
const url = auth.hasRole('admin') ? '/admin/dashboard' : '/dashboard'
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 text-center">
    <p class="text-5xl font-bold text-indigo-600">403</p>
    <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">Accesso negato</h1>
    <p class="mt-2 text-slate-600">Non hai i permessi per vedere questa pagina.</p>

    <RouterLink
      :to="url"
      class="mt-6 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
    >
      Torna alla dashboard
    </RouterLink>
  </div>
</template>