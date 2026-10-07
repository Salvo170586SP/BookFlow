export interface BookCopy {
  title: string
}

export interface CopyLoan {
  barcode: string
  book: BookCopy
}

export interface MemberLoan {
  id: number
  loaned_at: string
  due_at: string
  copy: CopyLoan
}

export interface MemberDetail {
  phone: string | null
  address: string | null
  birth_city: string | null
  birth_date: string | null
  photo: string | null
}

export interface Member {
  id: number
  name: string
  email: string
  created_at: string
  loans_count: number
  loans: MemberLoan[]
  detail: MemberDetail | null
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
