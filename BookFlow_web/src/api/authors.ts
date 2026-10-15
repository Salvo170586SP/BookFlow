import http from '@/api/http'
import type { Named } from '@/types/book'
import type { Author } from '@/types/author'
import type { Paginated } from '@/types/author'

export async function fetchAuthors(page = 1, search?: string): Promise<Paginated<Author>> {
  const { data } = await http.get<Paginated<Author>>('/authors', { params: { page, search } })
  return data
}

export async function fetchAuthor(authorId: number): Promise<Author> {
    const { data } = await http.get<{ data: Author }>(`/authors/${authorId}`)
    return data.data
}

export async function fetchAuthorsOptions(): Promise<Named[]> {
    const { data } = await http.get<{ data: Named[] }>('/authors/options')
    return data.data
}