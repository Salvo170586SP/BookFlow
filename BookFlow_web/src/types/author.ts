// Un elemento con id e nome (editore, categoria, autore)
export interface AuthorBook {
  id: number
  title: string
}

export interface Author {
  id: number
  name: string
  bio: string | null
  books: AuthorBook[]
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