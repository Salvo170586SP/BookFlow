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

export async function fetchBookCreated(
  page = 1,
  categoryId?: number,
  search?: string,
  publishedYear?: number
): Promise<Paginated<Book>> {
  const { data } = await http.get<Paginated<Book>>(`/me/books-created`, {
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
  if (payload.cover instanceof File) {
    await http.post('/books', toBookFormData(payload))
    return
  }

  await http.post('/books', payload)
}

export async function updateBook(payload: BookStore, bookId: number) {
  await getCsrfCookie()
  if (payload.cover instanceof File) {
    const formData = toBookFormData(payload)
    formData.append('_method', 'PUT')
    await http.post(`/books/${bookId}`, formData)
    await fetchBooks()
    return
  }

  await http.put(`/books/${bookId}`, payload)
  await fetchBooks()
}

function toBookFormData(payload: BookStore): FormData {
  const formData = new FormData()

  for (const [key, value] of Object.entries(payload)) {
    if (value === null || value === undefined) continue

    if (Array.isArray(value)) {
      for (const item of value) {
        formData.append(`${key}[]`, String(item))
      }
    } else {
      formData.append(key, value)
    }
  }

  return formData
}
