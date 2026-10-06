import http from '@/api/http'
import type { Book, Paginated } from '@/types/book'

export async function fetchBooks(page = 1, categoryId?: number): Promise<Paginated<Book>> {
  const { data } = await http.get<Paginated<Book>>('/books', {
    params: { page, category_id: categoryId },
  })
  return data
}

export async function fetchBook(bookId: number): Promise<Book> {
  const { data } = await http.get<{ data: Book }>(`/books/${bookId}`)
  return data.data
}