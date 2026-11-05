<script setup>
const props = defineProps({
  meta: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  adminTheme: { type: Boolean, default: false },
})

const emit = defineEmits(['change'])

function goToPage(page) {
  if (props.loading) return
  if (page < 1 || page > props.meta.last_page) return
  emit('change', page)
}
</script>

<template>
  <div
    v-if="meta"
    class="mt-4 flex items-center justify-between text-sm"
    :class="adminTheme ? 'text-[#55594e]' : 'text-slate-600'"
  >
    <p class="text-xs font-medium">Pagina {{ meta.current_page }} di {{ meta.last_page }} · {{ meta.total }}</p>

    <div class="flex gap-2">
      <button
        :disabled="meta.current_page <= 1 || loading"
        class="cursor-pointer px-3 py-1.5 font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
        :class="
          adminTheme
            ? 'border border-[#d45b3e] bg-[#d45b3e] text-[#fffdf8] hover:bg-[#b94731]'
            : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
        "
        @click="goToPage(meta.current_page - 1)"
      >
        Precedente
      </button>
      <button
        :disabled="meta.current_page >= meta.last_page || loading"
        class="cursor-pointer px-3 py-1.5 font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
        :class="
          adminTheme
            ? 'border border-[#d45b3e] bg-[#d45b3e] text-[#fffdf8] hover:bg-[#b94731]'
            : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
        "
        @click="goToPage(meta.current_page + 1)"
      >
        Successiva
      </button>
    </div>
  </div>
</template>