import { useParams, useNavigate } from 'react-router-dom'
import { ArrowLeft, Edit } from 'lucide-react'
import { useState } from 'react'
import { useAccount } from '@/hooks/useAccounts'
import Button from '@/components/ui/Button'
import AccountFormModal from './AccountFormModal'
import { format } from 'date-fns'

const AccountDetailPage = () => {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [isEditModalOpen, setIsEditModalOpen] = useState(false)

  const { data: account, isLoading, error } = useAccount(id!)

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
      </div>
    )
  }

  if (error || !account) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading account</p>
        <Button onClick={() => navigate('/accounts')} className="mt-4">
          Back to Accounts
        </Button>
      </div>
    )
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => navigate('/accounts')}
          className="flex items-center text-gray-600 hover:text-gray-900 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Accounts
        </button>

        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900">{account.name}</h1>
          <Button onClick={() => setIsEditModalOpen(true)}>
            <Edit className="w-4 h-4 mr-2" />
            Edit
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Account Information</h2>
          <dl className="space-y-3">
            <div>
              <dt className="text-sm font-medium text-gray-500">Code</dt>
              <dd className="mt-1 text-sm text-gray-900">{account.code}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Name</dt>
              <dd className="mt-1 text-sm text-gray-900">{account.name}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Type</dt>
              <dd className="mt-1 text-sm text-gray-900 capitalize">{account.type}</dd>
            </div>
            {account.category && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Category</dt>
                <dd className="mt-1 text-sm text-gray-900">{account.category}</dd>
              </div>
            )}
            <div>
              <dt className="text-sm font-medium text-gray-500">Status</dt>
              <dd className="mt-1">
                <span
                  className={`px-2 py-1 text-xs font-semibold rounded-full ${
                    account.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800'
                  }`}
                >
                  {account.is_active ? 'Active' : 'Inactive'}
                </span>
              </dd>
            </div>
          </dl>
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Balance Information</h2>
          <dl className="space-y-3">
            {account.opening_balance !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Opening Balance</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  ${account.opening_balance.toLocaleString()}
                </dd>
              </div>
            )}
            {account.balance !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Current Balance</dt>
                <dd
                  className={`mt-1 text-lg font-semibold ${
                    account.balance < 0 ? 'text-red-600' : 'text-green-600'
                  }`}
                >
                  ${account.balance.toLocaleString()}
                </dd>
              </div>
            )}
            <div>
              <dt className="text-sm font-medium text-gray-500">Created At</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(account.created_at), 'PPpp')}
              </dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Updated At</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(account.updated_at), 'PPpp')}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      {isEditModalOpen && (
        <AccountFormModal
          isOpen={isEditModalOpen}
          onClose={() => setIsEditModalOpen(false)}
          account={account}
        />
      )}
    </div>
  )
}

export default AccountDetailPage
