// Un elemento con id e nome (editore, categoria, autore)
export interface Named {
  id: number
  name: string
}

export interface BookCopy {
  id: number
  barcode: string
  status: string
}

export interface Book {
  id: number
  title: string
  isbn: string
  published_year: number | null
  publisher: Named | null
  category: Named
  authors: Named[]
  copies?: BookCopy[]
  available_copies_count: number
}

// Informazioni sulla paginazione, dentro la chiave "meta" della risposta
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Risposta paginata generica: servirà anche per autori, prestiti, ecc.
export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}