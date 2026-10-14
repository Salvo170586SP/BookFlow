<script setup lang="ts">
import { RouterView } from 'vue-router'
import './style.css'
import { useNotificationStore } from './stores/notification'

const notification = useNotificationStore()
</script>

<template>
  <div>
    <RouterView />

    <div
      v-if="notification.message"
      role="alert"
      class="fixed bottom-4 right-4 z-50 max-w-sm cursor-pointer rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
      :class="notification.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
      @click="notification.clear()"
    >
      {{ notification.message }}
    </div>
  </div>
</template>