import { useParams, useNavigate } from 'react-router-dom'
import { ArrowLeft, Edit } from 'lucide-react'
import { useState } from 'react'
import { useProject } from '@/hooks/useProjects'
import { format } from 'date-fns'
import Button from '@/components/ui/Button'
import ProjectFormModal from './ProjectFormModal'

const ProjectDetailPage = () => {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [isEditModalOpen, setIsEditModalOpen] = useState(false)

  const { data: project, isLoading, error } = useProject(id!)

  if (isLoading) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
      </div>
    )
  }

  if (error || !project) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">Error loading project</p>
        <Button onClick={() => navigate('/projects')} className="mt-4">
          Back to Projects
        </Button>
      </div>
    )
  }

  const statusColors = {
    planned: 'bg-gray-100 text-gray-800',
    active: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    on_hold: 'bg-yellow-100 text-yellow-800',
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => navigate('/projects')}
          className="flex items-center text-gray-600 hover:text-gray-900 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Projects
        </button>

        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{project.name}</h1>
            <span
              className={`inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full ${
                statusColors[project.status]
              }`}
            >
              {project.status.replace('_', ' ').toUpperCase()}
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
          <h2 className="text-lg font-semibold mb-4">Project Information</h2>
          <dl className="space-y-3">
            <div>
              <dt className="text-sm font-medium text-gray-500">Code</dt>
              <dd className="mt-1 text-sm text-gray-900">{project.code}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500">Name</dt>
              <dd className="mt-1 text-sm text-gray-900">{project.name}</dd>
            </div>
            {project.client_name && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Client</dt>
                <dd className="mt-1 text-sm text-gray-900">{project.client_name}</dd>
              </div>
            )}
            {project.description && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Description</dt>
                <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                  {project.description}
                </dd>
              </div>
            )}
            {project.start_date && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Start Date</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {format(new Date(project.start_date), 'PPP')}
                </dd>
              </div>
            )}
            {project.end_date && (
              <div>
                <dt className="text-sm font-medium text-gray-500">End Date</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {format(new Date(project.end_date), 'PPP')}
                </dd>
              </div>
            )}
          </dl>
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-4">Financial Summary</h2>
          <dl className="space-y-3">
            {project.budget !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Budget</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  ${project.budget.toLocaleString()}
                </dd>
              </div>
            )}
            {project.total_income !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Total Income</dt>
                <dd className="mt-1 text-sm text-green-600">
                  ${project.total_income.toLocaleString()}
                </dd>
              </div>
            )}
            {project.total_expense !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Total Expenses</dt>
                <dd className="mt-1 text-sm text-red-600">
                  ${project.total_expense.toLocaleString()}
                </dd>
              </div>
            )}
            {project.balance !== undefined && (
              <div>
                <dt className="text-sm font-medium text-gray-500">Balance</dt>
                <dd
                  className={`mt-1 text-lg font-semibold ${
                    project.balance < 0 ? 'text-red-600' : 'text-green-600'
                  }`}
                >
                  ${project.balance.toLocaleString()}
                </dd>
              </div>
            )}
            <div>
              <dt className="text-sm font-medium text-gray-500">Created At</dt>
              <dd className="mt-1 text-sm text-gray-900">
                {format(new Date(project.created_at), 'PPpp')}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      {isEditModalOpen && (
        <ProjectFormModal
          isOpen={isEditModalOpen}
          onClose={() => setIsEditModalOpen(false)}
          project={project}
        />
      )}
    </div>
  )
}

export default ProjectDetailPage
