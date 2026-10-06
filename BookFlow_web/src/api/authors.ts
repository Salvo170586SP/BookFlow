import http from '@/api/http'
import type { Author } from '@/types/author'
import type { Paginated } from '@/types/author'

export async function fetchAuthors(page = 1): Promise<Paginated<Author>> {
    const { data } = await http.get<Paginated<Author>>('/authors', { params: { page } })
    return data
}

export async function fetchAuthor(authorId: number): Promise<Author> {
    const { data } = await http.get<{ data: Author }>(`/authors/${authorId}`)
    return data.data
}