import { ref } from 'vue'
import { defineStore } from 'pinia'

type NotificationType = 'error' | 'success'

export const useNotificationStore = defineStore('notification', () => {
    const message = ref<string | null>(null)
    const type = ref<NotificationType>('success')
    let timer: ReturnType<typeof setTimeout> | undefined

    function show(text: string, kind: NotificationType = 'success', duration = 4000) {
        message.value = text
        type.value = kind

        // Se c'è già un timer attivo lo annulliamo, così un nuovo messaggio non sparisce troppo presto
        clearTimeout(timer)
        timer = setTimeout(clear, duration)
    }

    function clear() {
        message.value = null
    }

    return { message, type, show, clear }
})