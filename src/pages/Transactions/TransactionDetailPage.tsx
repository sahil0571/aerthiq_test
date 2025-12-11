import { useParams, useNavigate } from 'react-router-dom'
import { ArrowLeft, Edit } from 'lucide-react'
import { useState } from 'react'
import { useTransaction } from '@/hooks/useTransactions'
import { format } from 'date-fns'
import Button from '@/components/ui/Button'
import TransactionFormModal from './TransactionFormModal'

const TransactionDetailPage = () => {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [isEditModalOpen, setIsEditModalOpen] = useState(false)

  const { data: transaction, isLoading, error } = useTransaction(id!)

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
      </div>
    )
  }

  if (error || !transaction) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading transaction</p>
        <Button onClick={() => navigate('/transactions')} className="mt-4">
          Back to Transactions
        </Button>
      </div>
    )
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => navigate('/transactions')}
          className="flex items-center text-gray-600 hover:text-gray-900 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Transactions
        </button>

        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900">{transaction.description}</h1>
          <Button onClick={() => setIsEditModalOpen(true)}>
            <Edit className="w-4 h-4 mr-2" />
            Edit
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Transaction Information</h2>
          <dl className="space-y-3">
            <div>
              <dt className="text-sm font-medium text-gray-500">Date</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(transaction.date), 'PPP')}
              </dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Description</dt>
              <dd className="mt-1 text-sm text-gray-900">{transaction.description}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Type</dt>
              <dd className="mt-1">
                <span
                  className={`px-2 py-1 text-xs font-semibold rounded-full ${
                    transaction.transaction_type === 'debit'
                      ? 'bg-red-100 text-red-800'
                      : 'bg-green-100 text-green-800'
                  }`}
                >
                  {transaction.transaction_type.toUpperCase()}
                </span>
              </dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Amount</dt>
              <dd
                className={`mt-1 text-lg font-semibold ${
                  transaction.transaction_type === 'debit'
                    ? 'text-red-600'
                    : 'text-green-600'
                }`}
              >
                {transaction.transaction_type === 'debit' ? '-' : '+'}$
                {transaction.amount.toLocaleString()}
              </dd>
            </div>
            {transaction.category && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Category</dt>
                <dd className="mt-1 text-sm text-gray-900">{transaction.category}</dd>
              </div>
            )}
            {transaction.reference && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Reference</dt>
                <dd className="mt-1 text-sm text-gray-900">{transaction.reference}</dd>
              </div>
            )}
          </dl>
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Related Information</h2>
          <dl className="space-y-3">
            <div>
              <dt className="text-sm font-medium text-gray-500">Account</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {transaction.account?.name || transaction.account_id}
              </dd>
            </div>
            {transaction.project && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Project</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  <button
                    onClick={() => navigate(`/projects/${transaction.project_id}`)}
                    className="text-primary-600 hover:text-primary-800"
                  >
                    {transaction.project.name}
                  </button>
                </dd>
              </div>
            )}
            {transaction.employee && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Employee</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  <button
                    onClick={() => navigate(`/employees/${transaction.employee_id}`)}
                    className="text-primary-600 hover:text-primary-800"
                  >
                    {transaction.employee.first_name} {transaction.employee.last_name}
                  </button>
                </dd>
              </div>
            )}
            {transaction.notes && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Notes</dt>
                <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                  {transaction.notes}
                </dd>
              </div>
            )}
            <div>
              <dt className="text-sm font-medium text-gray-500">Created At</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(transaction.created_at), 'PPpp')}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      {isEditModalOpen && (
        <TransactionFormModal
          isOpen={isEditModalOpen}
          onClose={() => setIsEditModalOpen(false)}
          transaction={transaction}
        />
      )}
    </div>
  )
}

export default TransactionDetailPage
