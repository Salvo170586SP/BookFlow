import http, { getCsrfCookie } from '@/api/http'
import type { Category } from '@/types/category'
import type { Named, Paginated } from '@/types/book'

interface StorePayload {
    name: string
}

export async function fetchCategories(page = 1): Promise<Paginated<Category>> {
    const { data } = await http.get<Paginated<Category>>('/categories', { params: { page } })
    return data
}

export async function fetchCategoriesOptions(): Promise<Named[]> {
    const { data } = await http.get<{ data: Named[] }>('/categories/options')
    return data.data
}

export async function fetchCategory(id: number): Promise<Category> {
    const { data } = await http.get<{ data: Category }>(`/categories/${id}`)
    return data.data
}

export async function storeCategories(payload: StorePayload) {
    await getCsrfCookie()
    await http.post('/categories', payload)
    await fetchCategories()
}

export async function updateCategories(payload: StorePayload, id: number) {
    await getCsrfCookie()
    await http.put(`/categories/${id}`, payload)
    await fetchCategories()
}

export async function destroyCategories(id: number) {
    await getCsrfCookie()
    await http.delete(`/categories/${id}`)
    await fetchCategories()
}