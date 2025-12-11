import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Plus, Edit, Trash2 } from 'lucide-react'
import { useProjects, useDeleteProject } from '@/hooks/useProjects'
import Table from '@/components/ui/Table'
import Button from '@/components/ui/Button'
import FilterBar from '@/components/filters/FilterBar'
import ProjectFormModal from './ProjectFormModal'
import type { Project, FilterParams } from '@/types'

const ProjectsPage = () => {
  const navigate = useNavigate()
  const [filters, setFilters] = useState<FilterParams>({})
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
  const [editingProject, setEditingProject] = useState<Project | null>(null)

  const { data, isLoading, error } = useProjects(filters)
  const deleteMutation = useDeleteProject()

  const handleDelete = async (id: string) => {
    if (window.confirm('Are you sure you want to delete this project?')) {
      try {
        await deleteMutation.mutateAsync(id)
      } catch (err) {
        alert('Failed to delete project')
      }
    }
  }

  const statusOptions = [
    { value: 'planned', label: 'Planned' },
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'on_hold', label: 'On Hold' },
  ]

  const columns = [
    { key: 'code', header: 'Code' },
    { key: 'name', header: 'Name' },
    { key: 'client_name', header: 'Client' },
    {
      key: 'status',
      header: 'Status',
      render: (project: Project) => {
        const statusColors = {
          planned: 'bg-gray-100 text-gray-800',
          active: 'bg-blue-100 text-blue-800',
          completed: 'bg-green-100 text-green-800',
          on_hold: 'bg-yellow-100 text-yellow-800',
        }
        return (
          <span
            className={`px-2 py-1 text-xs font-semibold rounded-full ${
              statusColors[project.status]
            }`}
          >
            {project.status.replace('_', ' ').toUpperCase()}
          </span>
        )
      },
    },
    {
      key: 'budget',
      header: 'Budget',
      render: (project: Project) =>
        project.budget ? `$${project.budget.toLocaleString()}` : '-',
      className: 'text-right',
    },
    {
      key: 'balance',
      header: 'Balance',
      render: (project: Project) =>
        project.balance !== undefined ? (
          <span className={project.balance < 0 ? 'text-red-600' : 'text-green-600'}>
            ${project.balance.toLocaleString()}
          </span>
        ) : (
          '-'
        ),
      className: 'text-right',
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (project: Project) => (
        <div className="flex space-x-2">
          <button
            onClick={(e) => {
              e.stopPropagation()
              setEditingProject(project)
            }}
            className="text-blue-600 hover:text-blue-800"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation()
              handleDelete(project.id)
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
        <p className="text-red-600">Error loading projects</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Projects</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          New Project
        </Button>
      </div>

      <FilterBar
        onFilter={setFilters}
        showStatus={true}
        statusOptions={statusOptions}
        showDateRange={false}
        showFinancialYear={false}
      />

      <div className="card">
        <Table
          data={data?.items || []}
          columns={columns}
          onRowClick={(project) => navigate(`/projects/${project.id}`)}
          isLoading={isLoading}
          emptyMessage="No projects found"
        />
      </div>

      <ProjectFormModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
      />

      {editingProject && (
        <ProjectFormModal
          isOpen={true}
          onClose={() => setEditingProject(null)}
          project={editingProject}
        />
      )}
    </div>
  )
}

export default ProjectsPage
