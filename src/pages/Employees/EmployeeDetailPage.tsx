import { useParams, useNavigate } from 'react-router-dom'
import { ArrowLeft, Edit } from 'lucide-react'
import { useState } from 'react'
import { useEmployee } from '@/hooks/useEmployees'
import { format } from 'date-fns'
import Button from '@/components/ui/Button'
import EmployeeFormModal from './EmployeeFormModal'

const EmployeeDetailPage = () => {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [isEditModalOpen, setIsEditModalOpen] = useState(false)

  const { data: employee, isLoading, error } = useEmployee(id!)

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
      </div>
    )
  }

  if (error || !employee) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading employee</p>
        <Button onClick={() => navigate('/employees')} className="mt-4">
          Back to Employees
        </Button>
      </div>
    )
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => navigate('/employees')}
          className="flex items-center text-gray-600 hover:text-gray-900 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Employees
        </button>

        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              {employee.first_name} {employee.last_name}
            </h1>
            <span
              className={`inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full ${
                employee.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800'
              }`}
            >
              {employee.is_active ? 'Active' : 'Inactive'}
            </span>
          </div>
          <Button onClick={() => setIsEditModalOpen(true)}>
            <Edit className="w-4 h-4 mr-2" />
            Edit
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Personal Information</h2>
          <dl className="space-y-3">
            <div>
              <dt className="text-sm font-medium text-gray-500">Employee Code</dt>
              <dd className="mt-1 text-sm text-gray-900">{employee.employee_code}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Name</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {employee.first_name} {employee.last_name}
              </dd>
            </div>
            {employee.email && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Email</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  <a
                    href={`mailto:${employee.email}`}
                    className="text-primary-600 hover:text-primary-800"
                  >
                    {employee.email}
                  </a>
                </dd>
              </div>
            )}
            {employee.phone && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Phone</dt>
                <dd className="mt-1 text-sm text-gray-900">{employee.phone}</dd>
              </div>
            )}
            <div>
              <dt className="text-sm font-medium text-gray-500">Created At</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(employee.created_at), 'PPpp')}
              </dd>
            </div>
          </dl>
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Employment Information</h2>
          <dl className="space-y-3">
            {employee.department && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Department</dt>
                <dd className="mt-1 text-sm text-gray-900">{employee.department}</dd>
              </div>
            )}
            {employee.position && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Position</dt>
                <dd className="mt-1 text-sm text-gray-900">{employee.position}</dd>
              </div>
            )}
            {employee.hire_date && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Hire Date</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {format(new Date(employee.hire_date), 'PPP')}
                </dd>
              </div>
            )}
            {employee.salary !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Salary</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  ${employee.salary.toLocaleString()}
                </dd>
              </div>
            )}
          </dl>
        </div>
      </div>

      <div className="card p-6">
        <h2 className="text-lg font-semibold mb-4">Financial Summary</h2>
        <dl className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {employee.total_paid !== undefined && (
            <div>
              <dt className="text-sm font-medium text-gray-500">Total Paid</dt>
              <dd className="mt-1 text-lg font-semibold text-green-600">
                ${employee.total_paid.toLocaleString()}
              </dd>
            </div>
          )}
          {employee.outstanding !== undefined && (
            <div>
              <dt className="text-sm font-medium text-gray-500">Outstanding</dt>
              <dd
                className={`mt-1 text-lg font-semibold ${
                  employee.outstanding > 0 ? 'text-red-600' : 'text-gray-900'
                }`}
              >
                ${employee.outstanding.toLocaleString()}
              </dd>
            </div>
          )}
          {employee.salary !== undefined && (
            <div>
              <dt className="text-sm font-medium text-gray-500">Monthly Salary</dt>
              <dd className="mt-1 text-lg font-semibold text-gray-900">
                ${employee.salary.toLocaleString()}
              </dd>
            </div>
          )}
        </dl>
      </div>

      {isEditModalOpen && (
        <EmployeeFormModal
          isOpen={isEditModalOpen}
          onClose={() => setIsEditModalOpen(false)}
          employee={employee}
        />
      )}
    </div>
  )
}

export default EmployeeDetailPage
