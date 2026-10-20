import { defineStore } from "pinia";
import { ref, computed } from "vue";
import http, { getCsrfCookie } from "@/api/http";

export interface User {
    id: number
    name: string
    email: string,
    roles: string[]
    loans: string[]
}

interface LoginPayload {
    email: string
    password: string
}

interface RegisterPayload extends LoginPayload {
    name: string
    password_confirmation: string
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    // true dopo il primo tentativo di recuperare l'utente (riuscito o no)
    const ready = ref(false)

    const isAuthenticated = computed(() => user.value !== null)

    const hasRole = (role: string) => user.value?.roles.includes(role) ?? false
    const hasAnyRole = (...roles: string[]) => roles.some(hasRole)

    // Recupera i dati dell'utente dal token salvato (serve dopo un refresh della pagina)
    async function fetchUser() {
        const { data } = await http.get<{ data: User }>('/me')
        user.value = data.data
    }
  
    // Chiede al server "chi sono?" una sola volta all'avvio dell'app
    async function init() {
        if (ready.value) return

        try {
            await fetchUser()
        } catch {
            user.value = null
        } finally {
            ready.value = true
        }
    }

    async function register(payload: RegisterPayload) {
        await getCsrfCookie()
        await http.post('/register', payload)
        await fetchUser()
    }

    async function login(payload: LoginPayload) {
        await getCsrfCookie()
        await http.post('/login', payload)
        await fetchUser()
    }

    async function logout() {
        try {
            await http.post('/logout')
        } finally {
            user.value = null
        }
    }

    return { user, ready, isAuthenticated, register, login, logout, fetchUser, init, hasRole, hasAnyRole}
})

