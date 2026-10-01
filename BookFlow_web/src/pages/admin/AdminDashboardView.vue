<script setup lang="ts">
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
</script>

<template>
  <main class="min-h-full bg-slate-50 px-4 py-10">
    <div class="mx-auto max-w-7xl">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">
        Benvenuto Admin,
        <span v-if="auth.user">{{ auth.user.name }}</span>
      </h1>
      <p class="mt-1 text-slate-600">Ecco il riepilogo del tuo account.</p>

      <div v-if="auth.user" class="mt-8 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-medium text-slate-500">Nome</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user.name }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-medium text-slate-500">Email</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ auth.user.email }}</p>
        </div>
      </div>
    </div>
  </main>
</template>