// Un elemento con id e nome (editore, categoria, autore)
 
export interface Category {
    id: number
    name: string
    books_count: number
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