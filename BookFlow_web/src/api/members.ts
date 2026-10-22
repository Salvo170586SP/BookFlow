import http, { getCsrfCookie } from '@/api/http'
import type { Member } from '@/types/member'
import type { Paginated } from '@/types/author'

interface UpdatePayload {
  name: string
  phone: string
  address: string
  birth_city: string
  birth_date: string
}

interface CreatedLoan {
  id: number
  due_at: string
  copy: {
    id: number
    barcode: string
  }
}

export async function fetchMembers(page = 1): Promise<Paginated<Member>> {
  const { data } = await http.get<Paginated<Member>>('/members', { params: { page } })
  return data
}

export async function fetchMember(memberId: number): Promise<Member> {
  const { data } = await http.get<{ data: Member }>(`/members/${memberId}`)
  return data.data
}

export async function updateMember(payload: UpdatePayload, memberId: number) {
  await getCsrfCookie()
  await http.put(`/members/${memberId}`, payload)
}

export async function destroyMember(memberId: number) {
  await getCsrfCookie()
  await http.delete(`/members/${memberId}`)
  await fetchMembers()
}

export async function destroyMemberLoan(memberId: number, loanId: number): Promise<void> {
  await getCsrfCookie()
  await http.delete(`/members/${memberId}/loans/${loanId}`)
}

export async function createMemberLoan(
  memberId: number,
  payload: { copy_id: number; due_at: string } | { book_id: number; due_at: string },
): Promise<CreatedLoan> {
  await getCsrfCookie()
  const { data } = await http.post<{ data: CreatedLoan }>(`/members/${memberId}/loans`, payload)
  return data.data
}