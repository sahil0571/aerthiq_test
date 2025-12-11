import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { accountsApi } from '@/lib/api'
import type { Account, FilterParams } from '@/types'

export const useAccounts = (params?: FilterParams) => {
  return useQuery({
    queryKey: ['accounts', params],
    queryFn: () => accountsApi.getAll(params),
  })
}

export const useAccount = (id: string) => {
  return useQuery({
    queryKey: ['accounts', id],
    queryFn: () => accountsApi.getById(id),
    enabled: !!id,
  })
}

export const useCreateAccount = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: (data: Partial<Account>) => accountsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['accounts'] })
    },
  })
}

export const useUpdateAccount = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: Partial<Account> }) =>
      accountsApi.update(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['accounts'] })
      queryClient.invalidateQueries({ queryKey: ['accounts', variables.id] })
    },
  })
}

export const useDeleteAccount = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: (id: string) => accountsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['accounts'] })
    },
  })
}
