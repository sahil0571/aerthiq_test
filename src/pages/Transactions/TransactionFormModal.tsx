import { useEffect, useState } from 'react'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import DatePicker from '@/components/ui/DatePicker'
import Button from '@/components/ui/Button'
import { useCreateTransaction, useUpdateTransaction } from '@/hooks/useTransactions'
import { useAccounts } from '@/hooks/useAccounts'
import { useProjects } from '@/hooks/useProjects'
import { useEmployees } from '@/hooks/useEmployees'
import type { Transaction, FormErrors } from '@/types'

interface TransactionFormModalProps {
  isOpen: boolean
  onClose: () => void
  transaction?: Transaction | null
}

const TransactionFormModal = ({ isOpen, onClose, transaction }: TransactionFormModalProps) => {
  const [formData, setFormData] = useState({
    date: '',
    description: '',
    amount: '',
    transaction_type: '',
    account_id: '',
    project_id: '',
    employee_id: '',
    category: '',
    reference: '',
    notes: '',
  })
  const [errors, setErrors] = useState<FormErrors>({})

  const createMutation = useCreateTransaction()
  const updateMutation = useUpdateTransaction()
  const { data: accountsData } = useAccounts({ size: 1000 })
  const { data: projectsData } = useProjects({ size: 1000 })
  const { data: employeesData } = useEmployees({ size: 1000 })

  useEffect(() => {
    if (transaction) {
      setFormData({
        date: transaction.date || '',
        description: transaction.description || '',
        amount: transaction.amount?.toString() || '',
        transaction_type: transaction.transaction_type || '',
        account_id: transaction.account_id || '',
        project_id: transaction.project_id || '',
        employee_id: transaction.employee_id || '',
        category: transaction.category || '',
        reference: transaction.reference || '',
        notes: transaction.notes || '',
      })
    } else {
      setFormData({
        date: new Date().toISOString().split('T')[0],
        description: '',
        amount: '',
        transaction_type: '',
        account_id: '',
        project_id: '',
        employee_id: '',
        category: '',
        reference: '',
        notes: '',
      })
    }
    setErrors({})
  }, [transaction, isOpen])

  const validate = () => {
    const newErrors: FormErrors = {}

    if (!formData.date) newErrors.date = 'Date is required'
    if (!formData.description.trim()) newErrors.description = 'Description is required'
    if (!formData.amount || parseFloat(formData.amount) <= 0)
      newErrors.amount = 'Amount must be greater than 0'
    if (!formData.transaction_type) newErrors.transaction_type = 'Type is required'
    if (!formData.account_id) newErrors.account_id = 'Account is required'

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!validate()) return

    try {
      const submitData = {
        date: formData.date,
        description: formData.description,
        amount: parseFloat(formData.amount),
        transaction_type: formData.transaction_type as 'debit' | 'credit',
        account_id: formData.account_id,
        project_id: formData.project_id || undefined,
        employee_id: formData.employee_id || undefined,
        category: formData.category || undefined,
        reference: formData.reference || undefined,
        notes: formData.notes || undefined,
      }

      if (transaction) {
        await updateMutation.mutateAsync({ id: transaction.id, data: submitData })
      } else {
        await createMutation.mutateAsync(submitData)
      }

      onClose()
    } catch (err) {
      alert('Failed to save transaction')
    }
  }

  const transactionTypes = [
    { value: 'debit', label: 'Debit' },
    { value: 'credit', label: 'Credit' },
  ]

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={transaction ? 'Edit Transaction' : 'Create Transaction'}
      size="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <DatePicker
            label="Date *"
            value={formData.date}
            onChange={(value) => setFormData({ ...formData, date: value })}
            error={errors.date}
          />

          <Select
            label="Type *"
            options={transactionTypes}
            value={formData.transaction_type}
            onChange={(e) => setFormData({ ...formData, transaction_type: e.target.value })}
            error={errors.transaction_type}
          />
        </div>

        <Input
          label="Description *"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          error={errors.description}
          placeholder="Payment for services"
        />

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Amount *"
            type="number"
            step="0.01"
            value={formData.amount}
            onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
            error={errors.amount}
            placeholder="0.00"
          />

          <Select
            label="Account *"
            options={
              accountsData?.items.map((a) => ({ value: a.id, label: a.name })) || []
            }
            value={formData.account_id}
            onChange={(e) => setFormData({ ...formData, account_id: e.target.value })}
            error={errors.account_id}
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Select
            label="Project"
            options={
              projectsData?.items.map((p) => ({ value: p.id, label: p.name })) || []
            }
            value={formData.project_id}
            onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
          />

          <Select
            label="Employee"
            options={
              employeesData?.items.map((e) => ({
                value: e.id,
                label: `${e.first_name} ${e.last_name}`,
              })) || []
            }
            value={formData.employee_id}
            onChange={(e) => setFormData({ ...formData, employee_id: e.target.value })}
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Category"
            value={formData.category}
            onChange={(e) => setFormData({ ...formData, category: e.target.value })}
            placeholder="Office Supplies"
          />

          <Input
            label="Reference"
            value={formData.reference}
            onChange={(e) => setFormData({ ...formData, reference: e.target.value })}
            placeholder="INV-001"
          />
        </div>

        <div>
          <label className="label">Notes</label>
          <textarea
            value={formData.notes}
            onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
            className="input min-h-[100px]"
            placeholder="Additional notes..."
          />
        </div>

        <div className="flex justify-end space-x-3 pt-4">
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button
            type="submit"
            isLoading={createMutation.isPending || updateMutation.isPending}
          >
            {transaction ? 'Update' : 'Create'}
          </Button>
        </div>
      </form>
    </Modal>
  )
}

export default TransactionFormModal
