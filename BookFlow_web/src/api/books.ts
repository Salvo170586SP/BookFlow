import http from '@/api/http'
import type { Book, Paginated } from '@/types/book'

export async function fetchBooks(page = 1, categoryId?: number): Promise<Paginated<Book>> {
  const { data } = await http.get<Paginated<Book>>('/books', {
    params: { page, category_id: categoryId },
  })
  return data
}