import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Plus, Edit, Trash2 } from 'lucide-react'
import { useTransactions, useDeleteTransaction } from '@/hooks/useTransactions'
import { format } from 'date-fns'
import Table from '@/components/ui/Table'
import Button from '@/components/ui/Button'
import FilterBar from '@/components/filters/FilterBar'
import TransactionFormModal from './TransactionFormModal'
import type { Transaction, FilterParams } from '@/types'

const TransactionsPage = () => {
  const navigate = useNavigate()
  const [filters, setFilters] = useState<FilterParams>({})
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
  const [editingTransaction, setEditingTransaction] = useState<Transaction | null>(null)

  const { data, isLoading, error } = useTransactions(filters)
  const deleteMutation = useDeleteTransaction()

  const handleDelete = async (id: string) => {
    if (window.confirm('Are you sure you want to delete this transaction?')) {
      try {
        await deleteMutation.mutateAsync(id)
      } catch (err) {
        alert('Failed to delete transaction')
      }
    }
  }

  const columns = [
    {
      key: 'date',
      header: 'Date',
      render: (transaction: Transaction) =>
        format(new Date(transaction.date), 'MMM dd, yyyy'),
    },
    { key: 'description', header: 'Description' },
    {
      key: 'account',
      header: 'Account',
      render: (transaction: Transaction) =>
        transaction.account?.name || transaction.account_id,
    },
    {
      key: 'project',
      header: 'Project',
      render: (transaction: Transaction) => transaction.project?.name || '-',
    },
    {
      key: 'transaction_type',
      header: 'Type',
      render: (transaction: Transaction) => (
        <span
          className={`px-2 py-1 text-xs font-semibold rounded-full ${
            transaction.transaction_type === 'debit'
              ? 'bg-red-100 text-red-800'
              : 'bg-green-100 text-green-800'
          }`}
        >
          {transaction.transaction_type.toUpperCase()}
        </span>
      ),
    },
    {
      key: 'amount',
      header: 'Amount',
      render: (transaction: Transaction) => (
        <span
          className={
            transaction.transaction_type === 'debit' ? 'text-red-600' : 'text-green-600'
          }
        >
          {transaction.transaction_type === 'debit' ? '-' : '+'}$
          {transaction.amount.toLocaleString()}
        </span>
      ),
      className: 'text-right',
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (transaction: Transaction) => (
        <div className="flex space-x-2">
          <button
            onClick={(e) => {
              e.stopPropagation()
              setEditingTransaction(transaction)
            }}
            className="text-blue-600 hover:text-blue-800"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation()
              handleDelete(transaction.id)
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
        <p className="text-red-600">Error loading transactions</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Transactions</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          New Transaction
        </Button>
      </div>

      <FilterBar
        onFilter={setFilters}
        showProject={true}
        showEmployee={true}
        showDateRange={true}
        showFinancialYear={true}
      />

      <div className="card">
        <Table
          data={data?.items || []}
          columns={columns}
          onRowClick={(transaction) => navigate(`/transactions/${transaction.id}`)}
          isLoading={isLoading}
          emptyMessage="No transactions found"
        />
      </div>

      <TransactionFormModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
      />

      {editingTransaction && (
        <TransactionFormModal
          isOpen={true}
          onClose={() => setEditingTransaction(null)}
          transaction={editingTransaction}
        />
      )}
    </div>
  )
}

export default TransactionsPage
