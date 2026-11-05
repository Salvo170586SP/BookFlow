<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import type { Member, PaginationMeta } from './../../../types/member'
import { destroyMember, fetchMembers } from './../../../api/members'
import ConfirmDeleteModal from '../../../components/ConfirmDeleteModal.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'
import PaginationComponent from '@/components/PaginationComponent.vue'

const route = useRoute()
const router = useRouter()

//conferma modale eliminazione
const showModal = ref(false) // modale aperta/chiusa
const memberToDelete = ref<Member | null>(null) // quale elemento sto per eliminare
const deleting = ref(false) // richiesta in corso

const members = ref<Member[]>([])
const meta = ref<PaginationMeta | null>(null)

//gestione caricamento dati api
const loading = ref(false)
const error = ref<string | null>(null)

const page = computed(() => Math.max(Number(route.query.page) || 1, 1))

// Contatore delle richieste: serve a ignorare le risposte arrivate in ritardo
let lastRequest = 0

async function load() {
  const requestId = ++lastRequest
  loading.value = true
  error.value = null

  try {
    const result = await fetchMembers(page.value)

    // Se nel frattempo è partita una richiesta più recente, questa risposta è vecchia
    if (requestId !== lastRequest) return

    if (result.meta.last_page > 0 && page.value > result.meta.last_page) {
      router.replace({ query: { ...route.query, page: result.meta.last_page } })
      return
    }

    members.value = result.data
    meta.value = result.meta
  } catch {
    if (requestId === lastRequest) {
      error.value = 'Impossibile caricare i membri. Riprova.'
    }
  } finally {
    if (requestId === lastRequest) {
      loading.value = false
    }
  }
}

function goToPage(nextPage: number) {
  router.push({ query: { ...route.query, page: nextPage } })
}

function formatMemberDate(date?: string | null): string {
  if (!date) return ''

  return new Intl.DateTimeFormat('it-IT', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

function askDelete(member: Member) {
  memberToDelete.value = member
  showModal.value = true
}

async function deleteMember() {
  if (!memberToDelete.value) return

  deleting.value = true

  try {
    await destroyMember(memberToDelete.value.id)
    await load()
    showModal.value = false // chiudo SOLO se è andata bene
    memberToDelete.value = null
  } catch (e) {
    console.error(e) // qui potresti mostrare un messaggio di errore
  } finally {
    deleting.value = false
  }
}

watch(page, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-[#f5f1e8]">
    <main class="p-10">
      <div class="flex items-center space-x-5">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Membri registrati</h1>
        <p
          v-if="members.length"
          class="inline-flex items-center justify-center rounded-lg bg-[#64704a] px-2 font-semibold text-[#f5f1e8]"
        >
          {{ members.length }}
        </p>
      </div>

      <p v-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <LoaderComponent v-if="loading && members.length === 0" admin-theme />

      <section v-else>

        <PaginationComponent :meta="meta" :loading="loading" admin-theme @change="goToPage" />

        <div
          class="mt-3 overflow-x-auto border border-slate-300 bg-white transition-opacity"
          :class="{ 'opacity-60': loading }"
        >
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide">
              <tr>
                <th class="px-4 py-3 w-80">Nome</th>
                <th class="px-4 py-3 w-80">Email</th>
                <th class="px-4 py-3 w-80">telefono</th>
                <th class="px-4 py-3 w-80">Indirizzo</th>
                <th class="px-4 py-3 w-80">Città</th>
                <th class="px-4 py-3 w-200">Data di Nascita</th>
                <th class="px-4 py-3 w-200">iscritto dal</th>
                <th class="px-4 py-3 w-200 text-center">Libri in prestito</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="member in members" :key="member.id">
                <td class="px-4 py-3 font-medium text-slate-900">{{ member.name }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ member.email }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.phone || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.address || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ member.detail?.birth_city || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ formatMemberDate(member.detail?.birth_date) || '-' }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ formatMemberDate(member.created_at) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                     class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-[#64704a] font-semibold text-[#f5f1e8]"
                  >
                    {{ member.loans_count }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="text-end space-x-1 flex">
                    <RouterLink
                      :to="`/admin/members/${member.id}/details`"
                      class="cursor-pointer border border-[#242820]/25 px-4 py-1 font-medium text-[#55594e] transition hover:bg-[#ede7da]"
                    >
                      Dettagli
                    </RouterLink>
                    <RouterLink
                      :to="`/admin/members/${member.id}/edit`"
                       class="cursor-pointer border border-[#64704a]/60 px-4 py-1 font-medium text-[#4f5b39] transition hover:bg-[#e8dfcd]"
                    >
                      Modifica
                    </RouterLink>
                    <button
                      @click="askDelete(member)"
                       class="cursor-pointer border border-[#8d3827]/50 px-4 py-1 font-medium text-[#8d3827] transition hover:bg-[#f5e4de]"
                    >
                      Elimina
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="members.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Nessun membro trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <ConfirmDeleteModal v-model="showModal" :loading="deleting" @confirm="deleteMember">
          Vuoi davvero eliminare
          <strong class="capitalize">{{ memberToDelete?.name }}</strong
          >?
        </ConfirmDeleteModal>

        <PaginationComponent :meta="meta" :loading="loading" admin-theme @change="goToPage" />
      </section>
    </main>
  </div>
</template>