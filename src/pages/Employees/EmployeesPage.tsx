import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Plus, Edit, Trash2 } from 'lucide-react'
import { useEmployees, useDeleteEmployee } from '@/hooks/useEmployees'
import Table from '@/components/ui/Table'
import Button from '@/components/ui/Button'
import FilterBar from '@/components/filters/FilterBar'
import EmployeeFormModal from './EmployeeFormModal'
import type { Employee, FilterParams } from '@/types'

const EmployeesPage = () => {
  const navigate = useNavigate()
  const [filters, setFilters] = useState<FilterParams>({})
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
  const [editingEmployee, setEditingEmployee] = useState<Employee | null>(null)

  const { data, isLoading, error } = useEmployees(filters)
  const deleteMutation = useDeleteEmployee()

  const handleDelete = async (id: string) => {
    if (window.confirm('Are you sure you want to delete this employee?')) {
      try {
        await deleteMutation.mutateAsync(id)
      } catch (err) {
        alert('Failed to delete employee')
      }
    }
  }

  const columns = [
    { key: 'employee_code', header: 'Code' },
    {
      key: 'name',
      header: 'Name',
      render: (employee: Employee) => `${employee.first_name} ${employee.last_name}`,
    },
    { key: 'email', header: 'Email' },
    { key: 'department', header: 'Department' },
    { key: 'position', header: 'Position' },
    {
      key: 'salary',
      header: 'Salary',
      render: (employee: Employee) =>
        employee.salary ? `$${employee.salary.toLocaleString()}` : '-',
      className: 'text-right',
    },
    {
      key: 'outstanding',
      header: 'Outstanding',
      render: (employee: Employee) =>
        employee.outstanding !== undefined ? (
          <span className={employee.outstanding > 0 ? 'text-red-600' : ''}>
            ${employee.outstanding.toLocaleString()}
          </span>
        ) : (
          '-'
        ),
      className: 'text-right',
    },
    {
      key: 'is_active',
      header: 'Status',
      render: (employee: Employee) => (
        <span
          className={`px-2 py-1 text-xs font-semibold rounded-full ${
            employee.is_active
              ? 'bg-green-100 text-green-800'
              : 'bg-gray-100 text-gray-800'
          }`}
        >
          {employee.is_active ? 'Active' : 'Inactive'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (employee: Employee) => (
        <div className="flex space-x-2">
          <button
            onClick={(e) => {
              e.stopPropagation()
              setEditingEmployee(employee)
            }}
            className="text-blue-600 hover:text-blue-800"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation()
              handleDelete(employee.id)
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
        <p className="text-red-600">Error loading employees</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Employees</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          New Employee
        </Button>
      </div>

      <FilterBar
        onFilter={setFilters}
        showFinancialYear={false}
        showDateRange={false}
      />

      <div className="card">
        <Table
          data={data?.items || []}
          columns={columns}
          onRowClick={(employee) => navigate(`/employees/${employee.id}`)}
          isLoading={isLoading}
          emptyMessage="No employees found"
        />
      </div>

      <EmployeeFormModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
      />

      {editingEmployee && (
        <EmployeeFormModal
          isOpen={true}
          onClose={() => setEditingEmployee(null)}
          employee={editingEmployee}
        />
      )}
    </div>
  )
}

export default EmployeesPage
