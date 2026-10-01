import http from '@/api/http'
import type { Book, Paginated } from '@/types/book'

export async function fetchBooks(page = 1): Promise<Paginated<Book>> {
  const { data } = await http.get<Paginated<Book>>('/books', { params: { page } })
  return data
}