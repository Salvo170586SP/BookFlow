import axios from 'axios'
import type { Router } from 'vue-router'
import http from '@/api/http'
import { useAuthStore } from '@/stores/auth'

export function setupInterceptors(router: Router) {
  http.interceptors.response.use(
    // Risposta riuscita: la lasciamo passare così com'è
    (response) => response,

    // Risposta con errore
    async (error) => {
      const auth = useAuthStore()

      const status = axios.isAxiosError(error) ? error.response?.status : undefined
      const sessionExpired = status === 401 || status === 419

      // Reagiamo solo se l'utente credeva di essere loggato
      if (sessionExpired && auth.isAuthenticated) {
        auth.user = null
        await router.push({ name: 'login' })
      }

      // Rilanciamo l'errore, così chi ha fatto la chiamata può comunque gestirlo
      return Promise.reject(error)
    },
  )
}