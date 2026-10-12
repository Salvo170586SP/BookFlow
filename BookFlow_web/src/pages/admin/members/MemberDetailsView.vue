<script setup lang="ts">
import { ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { destroyMemberLoan, fetchMember } from './../../../api/members'
import type { Member, MemberLoan } from './../../../types/member'
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue'
import LoaderComponent from '@/components/LoaderComponent.vue'

const showModal = ref(false)
const selectedLoanId = ref<number | null>(null)
const loanToDelete = ref('')
const deleting = ref(false)

function askDelete(loan: MemberLoan): void {
  selectedLoanId.value = loan.id
  loanToDelete.value = loan.loaned_at
  showModal.value = true
}

async function deleteLoan(): Promise<void> {
  const currentMember = member.value
  const loanId = selectedLoanId.value
  if (!currentMember || loanId === null) return

  deleting.value = true
  error.value = null
  try {
    await destroyMemberLoan(currentMember.id, loanId)
    currentMember.loans = currentMember.loans.filter((loan) => loan.id !== loanId)
    currentMember.loans_count = currentMember.loans.length
    showModal.value = false
    selectedLoanId.value = null
  } catch {
    error.value = 'Impossibile eliminare il prestito del membro.'
  } finally {
    deleting.value = false
  }
}

const route = useRoute()
const member = ref<Member | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
let requestId = 0

function formatLoanDate(date: string): string {
  return new Intl.DateTimeFormat('it-IT', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

async function load(): Promise<void> {
  const currentRequest = ++requestId

  const memberId = Number(route.params.id)
  member.value = null
  error.value = null

  if (!Number.isInteger(memberId) || memberId < 1) {
    error.value = 'ID membro non valido.'
    loading.value = false
    return
  }

  loading.value = true
  try {
    const result = await fetchMember(memberId)
    if (currentRequest !== requestId) return
    member.value = result
  } catch {
    if (currentRequest !== requestId) return

    error.value = 'Impossibile caricare i dettagli del membro.'
  } finally {
    if (currentRequest === requestId) loading.value = false
  }
}

watch(() => route.params.id, load, { immediate: true })
</script>

<template>
  <div class="min-h-full bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dettagli Membro</h1>
        <RouterLink to="/admin/members">Indietro</RouterLink>
      </div>

      <LoaderComponent v-if="loading" />

      <p v-else-if="error" class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </p>

      <section v-if="member" class="mt-6 border border-slate-400 bg-white p-6">
        <h2 class="text-lg font-semibold text-slate-900 capitalize">{{ member.name }}</h2>
        <div class="flex gap-50 mt-3 text-sm text-slate-600">
          <div>
            <p>
              Email:
              <span class="font-semibold">{{ member.email || 'Nessuna email disponibile.' }}</span>
            </p>
            <p>
              Iscritto dal:
              <span class="font-semibold">{{ formatLoanDate(member.created_at) }}</span>
            </p>
          </div>
          <div class="flex flex-col">
            <p>
              Telefono: <span class="font-semibold">{{ member.detail?.phone || '-' }}</span>
            </p>
            <p>
              Indirizzo: <span class="font-semibold">{{ member.detail?.address || '-' }}</span>
            </p>
          </div>
          <div>
            <p>
              Città di nascita:
              <span class="font-semibold">{{ member.detail?.birth_city || '-' }}</span>
            </p>
          </div>
        </div>
      </section>

      <section v-else class="mt-6 border border-slate-400 bg-white p-6">
        <p class="text-sm text-slate-600">Autore non trovato.</p>
      </section>

      <section v-if="member" class="mt-6 border border-slate-400 bg-white p-6">
        <div class="flex justify-between mb-5">
          <p class="text-lg font-semibold text-slate-900">Libri in prestito</p>
          <RouterLink class="border px-3 bg-gray-500 text-white hover:bg-gray-700" to="/admin/books"
            >Prenota</RouterLink
          >
        </div>

        <div class="mt-3 flex flex-wrap space-y-2">
          <table
            class="min-w-full divide-y divide-slate-200 text-left text-sm border border-slate-400"
          >
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Libro</th>
                <th class="px-4 py-3 w-80">BarCode</th>
                <th class="px-4 py-3">In prestito dal</th>
                <th class="px-4 py-3">va restituito entro</th>
                <th class="px-4 py-3 text-end">Azioni</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="loan in member.loans" :key="loan.id">
                <td class="px-4 py-3 text-slate-600">{{ loan.copy.book.title }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ loan.copy.barcode }}</td>
                <td class="px-4 py-3 text-slate-600">{{ formatLoanDate(loan.loaned_at) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ formatLoanDate(loan.due_at) }}</td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="text-end space-x-3">
                    <button
                      @click="askDelete(loan)"
                      class="bg-red-500 hover:bg-red-600 text-white cursor-pointer px-3 py-1 rounded-lg"
                    >
                      Elimina
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="member.loans.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-black">
                  Nessun prestito trovato.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <ConfirmDeleteModal v-model="showModal" :loading="deleting" @confirm="deleteLoan">
          <p class="text-sm">
            Vuoi davvero eliminare il prestito del {{ formatLoanDate(loanToDelete) }}?
          </p>
        </ConfirmDeleteModal>
      </section>
    </main>
  </div>
</template>