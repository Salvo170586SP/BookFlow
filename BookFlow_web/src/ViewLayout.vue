<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex min-h-screen flex-col bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <span class="text-lg font-semibold text-slate-900">MyApp</span>

        <div class="flex items-center space-x-5 text-sm font-medium">
          <RouterLink
            to="/login"
            class="text-slate-600 transition hover:text-indigo-600 aria-[current=page]:text-indigo-600"
          >
            Login
          </RouterLink>

          <RouterLink
            to="/register"
            class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white shadow-sm transition hover:bg-indigo-700"
          >
            Registrati
          </RouterLink>
        </div>
      </nav>
    </header>

    <main class="flex flex-1 flex-col">
      <div class="w-full h-full mt-20">
        <h1 class="text-center font-bold text-4xl">Mia App</h1>
      </div>
      <slot />
    </main>
  </div>
</template>

 
