<script setup>
defineProps({
  adminTheme: { type: Boolean, default: false },
})
</script>

<template>
  <div class="flex flex-col justify-center items-center">
    <div
      class="size-8 animate-spin rounded-full border-4 border-solid"
      :class="
        adminTheme
          ? 'border-[#242820]/20 border-t-[#d45b3e]'
          : 'border-gray-300 border-t-black'
      "
    ></div>
    <span :class="adminTheme ? 'text-[#55594e]' : 'text-black'">Caricamento...</span>
  </div>
</template>