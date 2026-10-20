import http from '@/api/http'
import type { MyLoan, Paginated } from '@/types/member'

export async function fetchMyLoans(page = 1): Promise<Paginated<MyLoan>> {
  const { data } = await http.get<Paginated<MyLoan>>('/me/loans', {
    params: { page },
  })

  return data
}