import axios from 'axios'
import type {
  Account,
  Transaction,
  Project,
  Employee,
  DashboardSummary,
  PaginatedResponse,
  FilterParams,
} from '@/types'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
  },
})

export const accountsApi = {
  getAll: (params?: FilterParams) =>
    api.get<PaginatedResponse<Account>>('/accounts', { params }).then(res => res.data),
  
  getById: (id: string) =>
    api.get<Account>(`/accounts/${id}`).then(res => res.data),
  
  create: (data: Partial<Account>) =>
    api.post<Account>('/accounts', data).then(res => res.data),
  
  update: (id: string, data: Partial<Account>) =>
    api.put<Account>(`/accounts/${id}`, data).then(res => res.data),
  
  delete: (id: string) =>
    api.delete(`/accounts/${id}`).then(res => res.data),
}

export const transactionsApi = {
  getAll: (params?: FilterParams) =>
    api.get<PaginatedResponse<Transaction>>('/transactions', { params }).then(res => res.data),
  
  getById: (id: string) =>
    api.get<Transaction>(`/transactions/${id}`).then(res => res.data),
  
  create: (data: Partial<Transaction>) =>
    api.post<Transaction>('/transactions', data).then(res => res.data),
  
  update: (id: string, data: Partial<Transaction>) =>
    api.put<Transaction>(`/transactions/${id}`, data).then(res => res.data),
  
  delete: (id: string) =>
    api.delete(`/transactions/${id}`).then(res => res.data),
}

export const projectsApi = {
  getAll: (params?: FilterParams) =>
    api.get<PaginatedResponse<Project>>('/projects', { params }).then(res => res.data),
  
  getById: (id: string) =>
    api.get<Project>(`/projects/${id}`).then(res => res.data),
  
  create: (data: Partial<Project>) =>
    api.post<Project>('/projects', data).then(res => res.data),
  
  update: (id: string, data: Partial<Project>) =>
    api.put<Project>(`/projects/${id}`, data).then(res => res.data),
  
  delete: (id: string) =>
    api.delete(`/projects/${id}`).then(res => res.data),
}

export const employeesApi = {
  getAll: (params?: FilterParams) =>
    api.get<PaginatedResponse<Employee>>('/employees', { params }).then(res => res.data),
  
  getById: (id: string) =>
    api.get<Employee>(`/employees/${id}`).then(res => res.data),
  
  create: (data: Partial<Employee>) =>
    api.post<Employee>('/employees', data).then(res => res.data),
  
  update: (id: string, data: Partial<Employee>) =>
    api.put<Employee>(`/employees/${id}`, data).then(res => res.data),
  
  delete: (id: string) =>
    api.delete(`/employees/${id}`).then(res => res.data),
}

export const dashboardApi = {
  getSummary: (params?: FilterParams) =>
    api.get<DashboardSummary>('/dashboard/summary', { params }).then(res => res.data),
}

export default api
