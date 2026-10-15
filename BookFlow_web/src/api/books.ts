import http, { getCsrfCookie } from '@/api/http'
import type { Book, BookCopy, BookStore, CopyStatus, Paginated } from '@/types/book'

export async function fetchBooks(
  page = 1,
  categoryId?: number,
  search?: string,
  publishedYear?: number,
): Promise<Paginated<Book>> {
  const { data } = await http.get<Paginated<Book>>('/books', {
    params: { page, category_id: categoryId, search, published_year: publishedYear },
  })
  return data
}

export async function fetchBookYears(): Promise<number[]> {
  const { data } = await http.get<{ data: number[] }>('/books/years')
  return data.data
}

export async function fetchBook(bookId: number): Promise<Book> {
  const { data } = await http.get<{ data: Book }>(`/books/${bookId}`)
  return data.data
}

export async function fetchAvailableCopies(bookId: number): Promise<BookCopy[]> {
  const { data } = await http.get<{ data: BookCopy[] }>(`/books/${bookId}/copies/available`)
  return data.data
}

export async function fetchBookCopies(bookId: number): Promise<BookCopy[]> {
  const { data } = await http.get<{ data: BookCopy[] }>(`/books/${bookId}/copies`)
  return data.data
}

export async function updateBookCopyStatus(
  bookId: number,
  copyId: number,
  status: CopyStatus,
): Promise<BookCopy> {
  await getCsrfCookie()
  const { data } = await http.put<{ data: BookCopy }>(`/books/${bookId}/copies/${copyId}`, {
    status,
  })
  return data.data
}

export async function destroyBookCopy(bookId: number, copyId: number): Promise<void> {
  await getCsrfCookie()
  await http.delete(`/books/${bookId}/copies/${copyId}`)
}

export async function destroyBooks(bookId: number) {
  await getCsrfCookie()
  await http.delete(`/books/${bookId}`)
  await fetchBooks()
}

interface StoreCopiesPayload {
  quantity: number
}

export async function storeBookCopies(
  bookId: number,
  payload: StoreCopiesPayload,
): Promise<number> {
  await getCsrfCookie()
  const { data } = await http.post<{ created_count: number }>(
    `/books/${bookId}/addCopy`,
    payload,
  )
  return data.created_count
}

export async function storeBooks(payload: BookStore) {
  await getCsrfCookie()
  await http.post('/books', payload)
  await fetchBooks()
}

export async function updateBook(payload: BookStore, bookId: number) {
  await getCsrfCookie()
  await http.put(`/books/${bookId}`, payload)
  await fetchBooks()
}