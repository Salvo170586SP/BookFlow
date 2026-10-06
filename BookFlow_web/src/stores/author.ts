import { defineStore } from "pinia";
import { ref } from "vue";
import { fetchAuthors as fetchAuthorsRequest } from "@/api/authors";
import type { Author, PaginationMeta } from "@/types/author";

export const useAuthorStore = defineStore('author', () => {

    // Lista autori della pagina corrente + info di paginazione
    const authors = ref<Author[]>([])
    const meta = ref<PaginationMeta | null>(null)

    async function fetchAuthors(page = 1): Promise<void> {
        const response = await fetchAuthorsRequest(page)
        authors.value = response.data
        meta.value = response.meta
    }



    return { authors, fetchAuthors }
})
