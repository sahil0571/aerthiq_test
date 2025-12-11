import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Plus, Edit, Trash2 } from 'lucide-react'
import { useAccounts, useDeleteAccount } from '@/hooks/useAccounts'
import Table from '@/components/ui/Table'
import Button from '@/components/ui/Button'
import FilterBar from '@/components/filters/FilterBar'
import AccountFormModal from './AccountFormModal'
import type { Account, FilterParams } from '@/types'

const AccountsPage = () => {
  const navigate = useNavigate()
  const [filters, setFilters] = useState<FilterParams>({})
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
  const [editingAccount, setEditingAccount] = useState<Account | null>(null)

  const { data, isLoading, error } = useAccounts(filters)
  const deleteMutation = useDeleteAccount()

  const handleDelete = async (id: string) => {
    if (window.confirm('Are you sure you want to delete this account?')) {
      try {
        await deleteMutation.mutateAsync(id)
      } catch (err) {
        alert('Failed to delete account')
      }
    }
  }

  const columns = [
    { key: 'code', header: 'Code' },
    { key: 'name', header: 'Name' },
    {
      key: 'type',
      header: 'Type',
      render: (account: Account) => (
        <span className="capitalize">{account.type}</span>
      ),
    },
    { key: 'category', header: 'Category' },
    {
      key: 'balance',
      header: 'Balance',
      render: (account: Account) => (
        <span className={account.balance && account.balance < 0 ? 'text-red-600' : ''}>
          ${account.balance?.toLocaleString() || '0.00'}
        </span>
      ),
      className: 'text-right',
    },
    {
      key: 'is_active',
      header: 'Status',
      render: (account: Account) => (
        <span
          className={`px-2 py-1 text-xs font-semibold rounded-full ${
            account.is_active
              ? 'bg-green-100 text-green-800'
              : 'bg-gray-100 text-gray-800'
          }`}
        >
          {account.is_active ? 'Active' : 'Inactive'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (account: Account) => (
        <div className="flex space-x-2">
          <button
            onClick={(e) => {
              e.stopPropagation()
              setEditingAccount(account)
            }}
            className="text-blue-600 hover:text-blue-800"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation()
              handleDelete(account.id)
            }}
            className="text-red-600 hover:text-red-800"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      ),
    },
  ]

  if (error) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading accounts</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Accounts</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          New Account
        </Button>
      </div>

      <FilterBar onFilter={setFilters} showFinancialYear={false} showDateRange={false} />

      <div className="card">
        <Table
          data={data?.items || []}
          columns={columns}
          onRowClick={(account) => navigate(`/accounts/${account.id}`)}
          isLoading={isLoading}
          emptyMessage="No accounts found"
        />
      </div>

      <AccountFormModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
      />

      {editingAccount && (
        <AccountFormModal
          isOpen={true}
          onClose={() => setEditingAccount(null)}
          account={editingAccount}
        />
      )}
    </div>
  )
}

export default AccountsPage
