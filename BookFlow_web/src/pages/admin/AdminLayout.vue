<script setup lang="ts">
import { useAuthStore } from '../../stores/auth'
import { RouterLink, useRoute, useRouter } from 'vue-router'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

function isActive(path: string) {
  return route.path === path || route.path.startsWith(`${path}/`)
}

async function logout() {
  await auth.logout()
  await router.push('/')
}
</script>

<template>
  <div class="h-screen bg-slate-50 flex">
    <section
      class="h-full w-80 border-e border-slate-300 bg-gray-200 text-gray-500 px-4 py-4 flex flex-col"
    >
      <div class="flex flex-1 flex-col">
        <span class="text-lg font-semibold w-full pb-3">BookFlow Manager</span>
 
        <div class="flex flex-1 flex-col space-y-5 border-t py-5 text-sm">
          <RouterLink
            class="cursor-pointer px-3 py-1.5 font-medium  transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/authors') }"
            to="/admin/authors"
          >
            Autori
          </RouterLink>

          <RouterLink
            class="cursor-pointer px-3 py-1.5 font-medium  transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/categories') }"
            to="/admin/categories"
          >
            Categorie
          </RouterLink>

          <RouterLink
            to="/admin/books"
            class="cursor-pointer px-3 py-1.5 font-medium transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/books') }"
          >
            Vai al catalogo
          </RouterLink>

          <hr />
          
          <RouterLink
            class="cursor-pointer px-3 py-1.5 font-medium transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/members') }"
            to="/admin/members"
          >
            Membri iscritti
          </RouterLink>

          <RouterLink
            class="cursor-pointer px-3 py-1.5 font-medium transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/loans') }"
            to=""
          >
            Prestiti
          </RouterLink>

          <RouterLink
            class="cursor-pointer px-3 py-1.5 font-medium transition hover:bg-gray-300"
            :class="{ 'bg-gray-300 text-gray-800 border border-gray-400': isActive('/admin/dashboard') }"
            to="/admin/dashboard"
          >
            Dashboard
          </RouterLink>
        </div>

        <button
          class="cursor-pointer px-3 py-1.5 text-start font-medium text-gray-500 transition hover:bg-gray-300"
          @click="logout"
        >
          Esci
        </button>
      </div>
    </section>

    <section class="overflow-auto h-full w-full">
     <RouterView></RouterView>
    </section>
  </div>
</template>