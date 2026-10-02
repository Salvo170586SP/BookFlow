import { defineStore } from "pinia";
import http, { getCsrfCookie } from "@/api/http";
import { ref } from "vue";

export interface Category {
    id: number
    name: string
}

interface StorePayload {
    name: string
}

export const useCategoryStore = defineStore('category', () => {

    // Recupera i dati dell'utente dal token salvato (serve dopo un refresh della pagina)
    const categories = ref<Category[]>([])

    async function fetchCategories() {
        const { data } = await http.get<{ data: Category[] }>('/categories')
        categories.value = data.data
    }

    async function fetchCategory(id: number) {
        const { data } = await http.get<{ data: Category }>(`/categories/${id}`)
        return data.data
    }

    async function store(payload: StorePayload) {
        await getCsrfCookie()
        await http.post('/categories', payload)
        await fetchCategories()
    }

    async function update(payload: StorePayload, id: number) {
        await getCsrfCookie()
        await http.put(`/categories/${id}`, payload)
        await fetchCategories()
    }

    async function destroy(id: number) {
        await getCsrfCookie()
        await http.delete(`/categories/${id}`)
        await fetchCategories()
    }


    return { categories, store, update, destroy, fetchCategories, fetchCategory }
})

