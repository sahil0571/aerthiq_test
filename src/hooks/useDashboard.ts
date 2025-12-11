import { useQuery } from '@tanstack/react-query'
import { dashboardApi } from '@/lib/api'
import type { FilterParams } from '@/types'

export const useDashboard = (params?: FilterParams) => {
  return useQuery({
    queryKey: ['dashboard', params],
    queryFn: () => dashboardApi.getSummary(params),
  })
}
