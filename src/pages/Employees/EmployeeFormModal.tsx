import { useEffect, useState } from 'react'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import DatePicker from '@/components/ui/DatePicker'
import Button from '@/components/ui/Button'
import { useCreateEmployee, useUpdateEmployee } from '@/hooks/useEmployees'
import type { Employee, FormErrors } from '@/types'

interface EmployeeFormModalProps {
  isOpen: boolean
  onClose: () => void
  employee?: Employee | null
}

const EmployeeFormModal = ({ isOpen, onClose, employee }: EmployeeFormModalProps) => {
  const [formData, setFormData] = useState({
    employee_code: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    department: '',
    position: '',
    hire_date: '',
    salary: '',
    is_active: true,
  })
  const [errors, setErrors] = useState<FormErrors>({})

  const createMutation = useCreateEmployee()
  const updateMutation = useUpdateEmployee()

  useEffect(() => {
    if (employee) {
      setFormData({
        employee_code: employee.employee_code || '',
        first_name: employee.first_name || '',
        last_name: employee.last_name || '',
        email: employee.email || '',
        phone: employee.phone || '',
        department: employee.department || '',
        position: employee.position || '',
        hire_date: employee.hire_date || '',
        salary: employee.salary?.toString() || '',
        is_active: employee.is_active ?? true,
      })
    } else {
      setFormData({
        employee_code: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        department: '',
        position: '',
        hire_date: '',
        salary: '',
        is_active: true,
      })
    }
    setErrors({})
  }, [employee, isOpen])

  const validate = () => {
    const newErrors: FormErrors = {}

    if (!formData.employee_code.trim()) newErrors.employee_code = 'Code is required'
    if (!formData.first_name.trim()) newErrors.first_name = 'First name is required'
    if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required'
    if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Invalid email format'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!validate()) return

    try {
      const submitData = {
        employee_code: formData.employee_code,
        first_name: formData.first_name,
        last_name: formData.last_name,
        email: formData.email || undefined,
        phone: formData.phone || undefined,
        department: formData.department || undefined,
        position: formData.position || undefined,
        hire_date: formData.hire_date || undefined,
        salary: formData.salary ? parseFloat(formData.salary) : undefined,
        is_active: formData.is_active,
      }

      if (employee) {
        await updateMutation.mutateAsync({ id: employee.id, data: submitData })
      } else {
        await createMutation.mutateAsync(submitData)
      }

      onClose()
    } catch (err) {
      alert('Failed to save employee')
    }
  }

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={employee ? 'Edit Employee' : 'Create Employee'}
      size="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <Input
          label="Employee Code *"
          value={formData.employee_code}
          onChange={(e) => setFormData({ ...formData, employee_code: e.target.value })}
          error={errors.employee_code}
          placeholder="EMP001"
        />

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="First Name *"
            value={formData.first_name}
            onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
            error={errors.first_name}
            placeholder="John"
          />

          <Input
            label="Last Name *"
            value={formData.last_name}
            onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
            error={errors.last_name}
            placeholder="Doe"
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Email"
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({ ...formData, email: e.target.value })}
            error={errors.email}
            placeholder="john.doe@example.com"
          />

          <Input
            label="Phone"
            type="tel"
            value={formData.phone}
            onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
            placeholder="+1 234 567 8900"
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Department"
            value={formData.department}
            onChange={(e) => setFormData({ ...formData, department: e.target.value })}
            placeholder="Engineering"
          />

          <Input
            label="Position"
            value={formData.position}
            onChange={(e) => setFormData({ ...formData, position: e.target.value })}
            placeholder="Software Engineer"
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <DatePicker
            label="Hire Date"
            value={formData.hire_date}
            onChange={(value) => setFormData({ ...formData, hire_date: value })}
          />

          <Input
            label="Salary"
            type="number"
            step="0.01"
            value={formData.salary}
            onChange={(e) => setFormData({ ...formData, salary: e.target.value })}
            placeholder="0.00"
          />
        </div>

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
            {employee ? 'Update' : 'Create'}
          </Button>
        </div>
      </form>
    </Modal>
  )
}

export default EmployeeFormModal
