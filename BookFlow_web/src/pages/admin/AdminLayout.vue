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
  <div class="flex min-h-screen bg-[#f5f1e8] text-[#242820]">
    <section
      class="sticky top-0 flex h-screen w-72 shrink-0 flex-col border-e border-[#242820]/20 bg-[#ede7da] px-4 py-5 text-[#55594e]"
    >
      <div class="flex flex-1 flex-col">
        <RouterLink
          to="/admin/dashboard"
          class="mb-5 flex items-center gap-3 border-b border-[#242820]/20 px-2 pb-5"
          aria-label="BookFlow Manager, dashboard"
        >
          <span class="grid size-10 shrink-0 place-items-center bg-[#d45b3e] text-[#f5f1e8]">
            <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
              <path
                d="M12 6.5C9.5 4.8 6.4 4.5 3 5.5v13c3.4-1 6.5-.7 9 1 2.5-1.7 5.6-2 9-1v-13c-3.4-1-6.5-.7-9 1Zm0 0v13"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linecap="square"
              />
            </svg>
          </span>
          <span>
            <span class="block text-lg font-black tracking-tight text-[#242820]">Bookflow</span>
            <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-[#64704a]"
              >Area manager</span
            >
          </span>
        </RouterLink>

        <div class="flex flex-1 flex-col gap-2 py-2 text-sm">
          <RouterLink
            to="/admin/books"
            class="flex cursor-pointer items-center gap-3 border-l-2 border-transparent px-3 py-2.5 font-semibold transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
            :class="{
              'border-[#d45b3e] bg-[#e8dfcd] text-[#242820]': isActive('/admin/books'),
            }"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
              />
            </svg>
            Catalogo
          </RouterLink>

          <RouterLink
            class="flex cursor-pointer items-center gap-3 border-l-2 border-transparent px-3 py-2.5 font-semibold transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
            :class="{
              'border-[#d45b3e] bg-[#e8dfcd] text-[#242820]': isActive('/admin/authors'),
            }"
            to="/admin/authors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
              />
            </svg>

            Autori
          </RouterLink>

          <RouterLink
            class="flex cursor-pointer items-center gap-3 border-l-2 border-transparent px-3 py-2.5 font-semibold transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
            :class="{
              'border-[#d45b3e] bg-[#e8dfcd] text-[#242820]': isActive('/admin/categories'),
            }"
            to="/admin/categories"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"
              />
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>

            Categorie
          </RouterLink>

          <hr class="my-3 border-t border-[#242820]/20" />

          <RouterLink
            class="flex cursor-pointer items-center gap-3 border-l-2 border-transparent px-3 py-2.5 font-semibold transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
            :class="{ 'border-[#d45b3e] bg-[#e8dfcd] text-[#242820]': isActive('/admin/members') }"
            to="/admin/members"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"
              />
            </svg>

            Membri iscritti
          </RouterLink>

          <RouterLink
            class="flex cursor-pointer items-center gap-3 border-l-2 border-transparent px-3 py-2.5 font-semibold transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
            :class="{ 'border-[#d45b3e] bg-[#e8dfcd] text-[#242820]': isActive('/admin/dashboard') }"
            to="/admin/dashboard"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z"
              />
            </svg>

            Dashboard
          </RouterLink>
        </div>

        <button
          class="flex cursor-pointer items-center gap-3 border-t border-[#242820]/20 px-3 py-4 text-start font-semibold text-[#55594e] transition-colors hover:bg-[#e8dfcd]/70 hover:text-[#242820]"
          @click="logout"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"
            />
          </svg>

          Esci
        </button>
      </div>
    </section>

    <section
      class="min-w-0 flex-1 overflow-auto
        [&_h1]:text-[#242820] [&_h2]:text-[#242820]
        [&_thead]:bg-[#64704a] [&_thead]:text-[#f5f1e8] [&_thead_tr]:bg-[#64704a]
        [&_tbody]:divide-[#242820]/10 [&_tbody_tr:hover]:bg-[#ede7da]/70
        [&_input:not([type=checkbox])]:border-[#242820]/25 [&_input:not([type=checkbox])]:bg-[#fffdf8] [&_input:not([type=checkbox])]:px-3 [&_input:not([type=checkbox])]:py-2
        [&_select]:border-[#242820]/25 [&_select]:bg-[#fffdf8] [&_select]:px-3 [&_select]:py-2
        [&_textarea]:border-[#242820]/25 [&_textarea]:bg-[#fffdf8] [&_textarea]:px-3 [&_textarea]:py-2
        [&_form]:border-[#242820]/20 [&_form]:bg-[#fffdf8]
        [&_section.border]:border-[#242820]/20 [&_section.border]:bg-[#fffdf8]"
    >
      <RouterView v-if="auth.hasRole('admin')"></RouterView>
    </section>
  </div>
</template>