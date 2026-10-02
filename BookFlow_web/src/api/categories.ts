import http from '@/api/http'
import type { Category } from '@/types/category'
import type { Paginated } from '@/types/book'

export async function fetchCategories(page = 1): Promise<Paginated<Category>> {
    const { data } = await http.get<Paginated<Category>>('/categories', { params: { page } })
    return data
}

export async function fetchCategoriesOptions(): Promise<Category[]> {
    const { data } = await http.get<{ data: Category[] }>('/categories/options')
    return data.data
}