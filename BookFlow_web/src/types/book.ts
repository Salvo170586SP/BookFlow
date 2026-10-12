// Un elemento con id e nome (editore, categoria, autore)
export interface Named {
  id: number
  name: string
}

export interface BookLoanUser {
  id: number
  name: string
  email?: string | null
}

export interface BookLoan {
  id: number
  user_id: number
  copy_id: number
  loaned_at: string
  due_at: string
  returned_at?: string | null
  user?: BookLoanUser | null
}

export interface BookCopy {
  id: number
  barcode: string
  status: CopyStatus
  loans?: BookLoan[]
}

export type CopyStatus = 'available' | 'damaged' | 'lost' | 'maintenance'

export interface Book {
  id: number
  title: string
  isbn: string
  published_year: number | null
  publisher: Named | null
  category: Named
  authors: Named[]
  copies?: BookCopy[]
  total_copies_count: number
  borrowed_copies_count: number
  remaining_copies_count: number
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