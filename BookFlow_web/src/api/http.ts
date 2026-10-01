import axios from 'axios'

const backendUrl = import.meta.env.VITE_API_URL

//con coockie di sessione
export const http = axios.create({
    baseURL: `${backendUrl}/api`,
    headers: { Accept: 'application/json' },
    withCredentials: true,
    withXSRFToken: true,
})

// Da chiamare prima di login e registrazione
export function getCsrfCookie() {
    return http.get('/sanctum/csrf-cookie', { baseURL: backendUrl })
}

export default http