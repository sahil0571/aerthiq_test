import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useDashboard } from '@/hooks/useDashboard'
import { TrendingUp, TrendingDown, Wallet, Briefcase, Users, DollarSign } from 'lucide-react'
import { format } from 'date-fns'
import FilterBar from '@/components/filters/FilterBar'
import type { FilterParams } from '@/types'

const DashboardPage = () => {
  const navigate = useNavigate()
  const [filters, setFilters] = useState<FilterParams>({})

  const { data: dashboard, isLoading, error } = useDashboard(filters)

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
      </div>
    )
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading dashboard</p>
      </div>
    )
  }

  const statCards = [
    {
      label: 'Total Assets',
      value: dashboard?.total_assets || 0,
      icon: Wallet,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
    },
    {
      label: 'Total Liabilities',
      value: dashboard?.total_liabilities || 0,
      icon: TrendingDown,
      color: 'text-red-600',
      bgColor: 'bg-red-100',
    },
    {
      label: 'Total Income',
      value: dashboard?.total_income || 0,
      icon: TrendingUp,
      color: 'text-green-600',
      bgColor: 'bg-green-100',
    },
    {
      label: 'Total Expenses',
      value: dashboard?.total_expenses || 0,
      icon: TrendingDown,
      color: 'text-orange-600',
      bgColor: 'bg-orange-100',
    },
    {
      label: 'Net Income',
      value: dashboard?.net_income || 0,
      icon: DollarSign,
      color: (dashboard?.net_income || 0) >= 0 ? 'text-green-600' : 'text-red-600',
      bgColor: (dashboard?.net_income || 0) >= 0 ? 'bg-green-100' : 'bg-red-100',
    },
    {
      label: 'Active Projects',
      value: dashboard?.active_projects || 0,
      icon: Briefcase,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100',
      isCount: true,
    },
    {
      label: 'Active Employees',
      value: dashboard?.active_employees || 0,
      icon: Users,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-100',
      isCount: true,
    },
  ]

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Financial overview and reports</p>
      </div>

      <FilterBar
        onFilter={setFilters}
        showFinancialYear={true}
        showDateRange={true}
        showProject={false}
        showEmployee={false}
      />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        {statCards.map((stat) => (
          <div key={stat.label} className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">{stat.label}</p>
                <p className={`text-2xl font-bold mt-2 ${stat.color}`}>
                  {stat.isCount
                    ? stat.value
                    : `$${stat.value.toLocaleString()}`}
                </p>
              </div>
              <div className={`${stat.bgColor} p-3 rounded-lg`}>
                <stat.icon className={`w-6 h-6 ${stat.color}`} />
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Project Summaries</h2>
          {dashboard?.project_summaries && dashboard.project_summaries.length > 0 ? (
            <div className="space-y-4">
              {dashboard.project_summaries.map((summary) => (
                <div
                  key={summary.project.id}
                  className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                  onClick={() => navigate(`/projects/${summary.project.id}`)}
                >
                  <div className="flex justify-between items-start mb-2">
                    <div>
                      <h3 className="font-semibold text-gray-900">
                        {summary.project.name}
                      </h3>
                      <p className="text-sm text-gray-600">{summary.project.code}</p>
                    </div>
                    <span
                      className={`px-2 py-1 text-xs font-semibold rounded-full ${
                        summary.project.status === 'active'
                          ? 'bg-blue-100 text-blue-800'
                          : summary.project.status === 'completed'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      }`}
                    >
                      {summary.project.status.toUpperCase()}
                    </span>
                  </div>
                  <div className="grid grid-cols-3 gap-4 mt-3 text-sm">
                    <div>
                      <p className="text-gray-600">Income</p>
                      <p className="font-semibold text-green-600">
                        ${summary.total_income.toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-600">Expense</p>
                      <p className="font-semibold text-red-600">
                        ${summary.total_expense.toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-600">Balance</p>
                      <p
                        className={`font-semibold ${
                          summary.balance >= 0 ? 'text-green-600' : 'text-red-600'
                        }`}
                      >
                        ${summary.balance.toLocaleString()}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500 text-center py-8">No project data available</p>
          )}
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Recent Transactions</h2>
          {dashboard?.recent_transactions && dashboard.recent_transactions.length > 0 ? (
            <div className="space-y-3">
              {dashboard.recent_transactions.map((transaction) => (
                <div
                  key={transaction.id}
                  className="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer transition-colors"
                  onClick={() => navigate(`/transactions/${transaction.id}`)}
                >
                  <div className="flex justify-between items-start">
                    <div>
                      <p className="font-medium text-gray-900">
                        {transaction.description}
                      </p>
                      <p className="text-xs text-gray-600 mt-1">
                        {format(new Date(transaction.date), 'MMM dd, yyyy')} •{' '}
                        {transaction.account?.name || transaction.account_id}
                      </p>
                    </div>
                    <div className="text-right">
                      <p
                        className={`font-semibold ${
                          transaction.transaction_type === 'debit'
                            ? 'text-red-600'
                            : 'text-green-600'
                        }`}
                      >
                        {transaction.transaction_type === 'debit' ? '-' : '+'}$
                        {transaction.amount.toLocaleString()}
                      </p>
                      <span
                        className={`text-xs px-2 py-0.5 rounded-full ${
                          transaction.transaction_type === 'debit'
                            ? 'bg-red-100 text-red-800'
                            : 'bg-green-100 text-green-800'
                        }`}
                      >
                        {transaction.transaction_type}
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500 text-center py-8">No recent transactions</p>
          )}
        </div>
      </div>

      <div className="card p-6">
        <h2 className="text-lg font-semibold mb-4">Financial Overview</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 className="text-sm font-medium text-gray-600 mb-3">Balance Sheet</h3>
            <dl className="space-y-2">
              <div className="flex justify-between">
                <dt className="text-sm text-gray-600">Assets</dt>
                <dd className="text-sm font-semibold text-gray-900">
                  ${(dashboard?.total_assets || 0).toLocaleString()}
                </dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-sm text-gray-600">Liabilities</dt>
                <dd className="text-sm font-semibold text-gray-900">
                  ${(dashboard?.total_liabilities || 0).toLocaleString()}
                </dd>
              </div>
              <div className="flex justify-between pt-2 border-t border-gray-200">
                <dt className="text-sm font-medium text-gray-900">Equity</dt>
                <dd className="text-sm font-semibold text-primary-600">
                  ${(dashboard?.total_equity || 0).toLocaleString()}
                </dd>
              </div>
            </dl>
          </div>
          <div>
            <h3 className="text-sm font-medium text-gray-600 mb-3">Income Statement</h3>
            <dl className="space-y-2">
              <div className="flex justify-between">
                <dt className="text-sm text-gray-600">Income</dt>
                <dd className="text-sm font-semibold text-green-600">
                  ${(dashboard?.total_income || 0).toLocaleString()}
                </dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-sm text-gray-600">Expenses</dt>
                <dd className="text-sm font-semibold text-red-600">
                  ${(dashboard?.total_expenses || 0).toLocaleString()}
                </dd>
              </div>
              <div className="flex justify-between pt-2 border-t border-gray-200">
                <dt className="text-sm font-medium text-gray-900">Net Income</dt>
                <dd
                  className={`text-sm font-semibold ${
                    (dashboard?.net_income || 0) >= 0
                      ? 'text-green-600'
                      : 'text-red-600'
                  }`}
                >
                  ${(dashboard?.net_income || 0).toLocaleString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  )
}

export default DashboardPage
