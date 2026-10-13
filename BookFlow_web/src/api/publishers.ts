import http from '@/api/http'
import type { Publisher } from '@/types/publisher'

export async function fetchPublishersOptions(): Promise<Publisher[]> {
  const { data } = await http.get<{ data: Publisher[] }>('/publishers/options')
  return data.data
}