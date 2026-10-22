<script setup lang="ts">
import { nextTick, onUnmounted, ref, watch } from 'vue'

const open = defineModel<boolean>({ default: false })
const closeButton = ref<HTMLButtonElement | null>(null)

function close(): void {
  open.value = false
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') close()
}

watch(open, async (isOpen) => {
  if (isOpen) {
    document.addEventListener('keydown', onKeydown)
    await nextTick()
    closeButton.value?.focus()
  } else {
    document.removeEventListener('keydown', onKeydown)
  }
})

onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      name="modal"
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/40 p-4"
        @click.self="close"
      >
        <div
          class="w-full max-w-4xl border border-[#242820]/25 bg-[#f5f1e8] p-6 text-[#242820] shadow-xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="review-modal-title"
        >
          <h2 id="review-modal-title" class="mb-3 text-2xl text-center font-black">
            Prenotazione confermata
          </h2>

          <div class="mb-6 leading-7 text-[#65675e] font-medium">
            <slot />
          </div>

          <div class="flex justify-end gap-2">
            <button
              ref="closeButton"
              class="border border-[#d45b3e]/40 bg-[#d45b3e]/15 px-5 py-2 text-sm font-bold transition-colors hover:bg-[#d45b3e]/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#d45b3e]"
              @click="close"
            >
              Ok
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

 