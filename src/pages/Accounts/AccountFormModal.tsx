import { useEffect, useState } from 'react'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Button from '@/components/ui/Button'
import { useCreateAccount, useUpdateAccount } from '@/hooks/useAccounts'
import type { Account, FormErrors } from '@/types'

interface AccountFormModalProps {
  isOpen: boolean
  onClose: () => void
  account?: Account | null
}

const AccountFormModal = ({ isOpen, onClose, account }: AccountFormModalProps) => {
  const [formData, setFormData] = useState({
    code: '',
    name: '',
    type: '',
    category: '',
    opening_balance: '',
    is_active: true,
  })
  const [errors, setErrors] = useState<FormErrors>({})

  const createMutation = useCreateAccount()
  const updateMutation = useUpdateAccount()

  useEffect(() => {
    if (account) {
      setFormData({
        code: account.code || '',
        name: account.name || '',
        type: account.type || '',
        category: account.category || '',
        opening_balance: account.opening_balance?.toString() || '',
        is_active: account.is_active ?? true,
      })
    } else {
      setFormData({
        code: '',
        name: '',
        type: '',
        category: '',
        opening_balance: '',
        is_active: true,
      })
    }
    setErrors({})
  }, [account, isOpen])

  const validate = () => {
    const newErrors: FormErrors = {}

    if (!formData.code.trim()) newErrors.code = 'Code is required'
    if (!formData.name.trim()) newErrors.name = 'Name is required'
    if (!formData.type) newErrors.type = 'Type is required'

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!validate()) return

    try {
      const submitData = {
        code: formData.code,
        name: formData.name,
        type: formData.type as 'asset' | 'liability' | 'equity' | 'income' | 'expense',
        category: formData.category || undefined,
        opening_balance: formData.opening_balance ? parseFloat(formData.opening_balance) : undefined,
        is_active: formData.is_active,
      }

      if (account) {
        await updateMutation.mutateAsync({ id: account.id, data: submitData })
      } else {
        await createMutation.mutateAsync(submitData)
      }

      onClose()
    } catch (err) {
      alert('Failed to save account')
    }
  }

  const accountTypes = [
    { value: 'asset', label: 'Asset' },
    { value: 'liability', label: 'Liability' },
    { value: 'equity', label: 'Equity' },
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
  ]

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={account ? 'Edit Account' : 'Create Account'}
      size="md"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <Input
          label="Code *"
          value={formData.code}
          onChange={(e) => setFormData({ ...formData, code: e.target.value })}
          error={errors.code}
          placeholder="ACC001"
        />

        <Input
          label="Name *"
          value={formData.name}
          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          error={errors.name}
          placeholder="Cash Account"
        />

        <Select
          label="Type *"
          options={accountTypes}
          value={formData.type}
          onChange={(e) => setFormData({ ...formData, type: e.target.value })}
          error={errors.type}
        />

        <Input
          label="Category"
          value={formData.category}
          onChange={(e) => setFormData({ ...formData, category: e.target.value })}
          placeholder="Current Assets"
        />

        <Input
          label="Opening Balance"
          type="number"
          step="0.01"
          value={formData.opening_balance}
          onChange={(e) => setFormData({ ...formData, opening_balance: e.target.value })}
          placeholder="0.00"
        />

        <div className="flex items-center">
          <input
            type="checkbox"
            id="is_active"
            checked={formData.is_active}
            onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
            className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
          />
          <label htmlFor="is_active" className="ml-2 block text-sm text-gray-900">
            Active
          </label>
        </div>

        <div className="flex justify-end space-x-3 pt-4">
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button
            type="submit"
            isLoading={createMutation.isPending || updateMutation.isPending}
          >
            {account ? 'Update' : 'Create'}
          </Button>
        </div>
      </form>
    </Modal>
  )
}

export default AccountFormModal
