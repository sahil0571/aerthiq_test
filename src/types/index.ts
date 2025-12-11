export interface Account {
  id: string
  code: string
  name: string
  type: 'asset' | 'liability' | 'equity' | 'income' | 'expense'
  category?: string
  balance?: number
  opening_balance?: number
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface Transaction {
  id: string
  date: string
  description: string
  amount: number
  transaction_type: 'debit' | 'credit'
  account_id: string
  account?: Account
  project_id?: string
  project?: Project
  employee_id?: string
  employee?: Employee
  category?: string
  reference?: string
  notes?: string
  created_at: string
  updated_at: string
}

export interface Project {
  id: string
  code: string
  name: string
  description?: string
  start_date?: string
  end_date?: string
  budget?: number
  status: 'planned' | 'active' | 'completed' | 'on_hold'
  client_name?: string
  total_income?: number
  total_expense?: number
  balance?: number
  created_at: string
  updated_at: string
}

export interface Employee {
  id: string
  employee_code: string
  first_name: string
  last_name: string
  email?: string
  phone?: string
  department?: string
  position?: string
  hire_date?: string
  salary?: number
  is_active: boolean
  total_paid?: number
  outstanding?: number
  created_at: string
  updated_at: string
}

export interface DashboardSummary {
  total_assets: number
  total_liabilities: number
  total_equity: number
  total_income: number
  total_expenses: number
  net_income: number
  active_projects: number
  active_employees: number
  recent_transactions: Transaction[]
  project_summaries: ProjectSummary[]
}

export interface ProjectSummary {
  project: Project
  total_income: number
  total_expense: number
  balance: number
}

export interface EmployeeSalaryHistory {
  employee: Employee
  total_paid: number
  outstanding: number
  recent_payments: Transaction[]
}

export interface PaginatedResponse<T> {
  items: T[]
  total: number
  page: number
  size: number
  pages: number
}

export interface FilterParams {
  financial_year?: string
  start_date?: string
  end_date?: string
  project_id?: string
  employee_id?: string
  account_id?: string
  type?: string
  status?: string
  search?: string
  page?: number
  size?: number
}

export interface FormErrors {
  [key: string]: string
}
