<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'

// 1. PROPS: i dati che il genitore passa al componente
const props = defineProps({
  title: { type: String, default: 'Conferma eliminazione' },
  message: {
    type: String,
    default: "Sei sicuro di voler eliminare questo elemento? L'azione non può essere annullata.",
  },
  confirmLabel: { type: String, default: 'Elimina' },
  cancelLabel: { type: String, default: 'Annulla' },
  loading: { type: Boolean, default: false },
})

// 2. v-model: aperto/chiuso (richiede Vue 3.4+)
const open = defineModel({ type: Boolean, default: false })

// 3. EMITS: gli eventi che il componente "lancia" al genitore
const emit = defineEmits(['confirm', 'cancel'])

const cancelBtn = ref<HTMLButtonElement | null>(null)

function close() {
  if (props.loading) return // durante la richiesta non si può chiudere
  open.value = false
  emit('cancel')
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}

// 4. Quando si apre: ascolto ESC e sposto il focus sul pulsante "Annulla"
watch(open, async (isOpen) => {
  if (isOpen) {
    document.addEventListener('keydown', onKeydown)
    await nextTick()
    cancelBtn.value?.focus()
  } else {
    document.removeEventListener('keydown', onKeydown)
  }
})

// 5. Pulizia, per evitare listener "orfani"
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      name="modal"
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-1"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-1000 flex items-center justify-center bg-black/20 p-4"
        @click.self="close"
      >
        <div
          class="w-full max-w-105 border border-[#242820]/25 bg-[#fffdf8] p-6 text-[#242820]"
          role="dialog"
          aria-modal="true"
          aria-labelledby="confirm-modal-title"
        >
          <h2 id="confirm-modal-title" class="mb-3 text-lg font-semibold">{{ title }}</h2>

          <div class="mb-6 text-[#55594e]">
            <!-- Slot: se il genitore passa contenuto, sostituisce il messaggio di default -->
            <slot>{{ message }}</slot>
          </div>

          <div class="flex justify-end gap-2">
            <button
              ref="cancelBtn"
              class="cursor-pointer border border-[#242820]/25 bg-[#ede7da] px-4 py-2 text-sm text-[#242820] transition hover:bg-[#e1d8c6] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#64704a] disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loading"
              @click="close"
            >
              {{ cancelLabel }}
            </button>
            <button
              class="cursor-pointer border border-[#8d3827] bg-[#8d3827] px-4 py-2 text-sm text-[#fffdf8] transition hover:bg-[#762f22] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#d45b3e] disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loading"
              @click="emit('confirm')"
            >
              {{ loading ? 'Eliminazione...' : confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

 