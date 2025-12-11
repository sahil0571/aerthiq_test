import { useEffect, useState } from 'react'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import DatePicker from '@/components/ui/DatePicker'
import Button from '@/components/ui/Button'
import { useCreateProject, useUpdateProject } from '@/hooks/useProjects'
import type { Project, FormErrors } from '@/types'

interface ProjectFormModalProps {
  isOpen: boolean
  onClose: () => void
  project?: Project | null
}

const ProjectFormModal = ({ isOpen, onClose, project }: ProjectFormModalProps) => {
  const [formData, setFormData] = useState({
    code: '',
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    budget: '',
    status: '',
    client_name: '',
  })
  const [errors, setErrors] = useState<FormErrors>({})

  const createMutation = useCreateProject()
  const updateMutation = useUpdateProject()

  useEffect(() => {
    if (project) {
      setFormData({
        code: project.code || '',
        name: project.name || '',
        description: project.description || '',
        start_date: project.start_date || '',
        end_date: project.end_date || '',
        budget: project.budget?.toString() || '',
        status: project.status || '',
        client_name: project.client_name || '',
      })
    } else {
      setFormData({
        code: '',
        name: '',
        description: '',
        start_date: '',
        end_date: '',
        budget: '',
        status: 'planned',
        client_name: '',
      })
    }
    setErrors({})
  }, [project, isOpen])

  const validate = () => {
    const newErrors: FormErrors = {}

    if (!formData.code.trim()) newErrors.code = 'Code is required'
    if (!formData.name.trim()) newErrors.name = 'Name is required'
    if (!formData.status) newErrors.status = 'Status is required'

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
        description: formData.description || undefined,
        start_date: formData.start_date || undefined,
        end_date: formData.end_date || undefined,
        budget: formData.budget ? parseFloat(formData.budget) : undefined,
        status: formData.status as 'planned' | 'active' | 'completed' | 'on_hold',
        client_name: formData.client_name || undefined,
      }

      if (project) {
        await updateMutation.mutateAsync({ id: project.id, data: submitData })
      } else {
        await createMutation.mutateAsync(submitData)
      }

      onClose()
    } catch (err) {
      alert('Failed to save project')
    }
  }

  const statusOptions = [
    { value: 'planned', label: 'Planned' },
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'on_hold', label: 'On Hold' },
  ]

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={project ? 'Edit Project' : 'Create Project'}
      size="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Code *"
            value={formData.code}
            onChange={(e) => setFormData({ ...formData, code: e.target.value })}
            error={errors.code}
            placeholder="PROJ001"
          />

          <Input
            label="Name *"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            error={errors.name}
            placeholder="Website Redesign"
          />
        </div>

        <Input
          label="Client Name"
          value={formData.client_name}
          onChange={(e) => setFormData({ ...formData, client_name: e.target.value })}
          placeholder="Acme Corporation"
        />

        <div>
          <label className="label">Description</label>
          <textarea
            value={formData.description}
            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
            className="input min-h-[100px]"
            placeholder="Project description..."
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <DatePicker
            label="Start Date"
            value={formData.start_date}
            onChange={(value) => setFormData({ ...formData, start_date: value })}
          />

          <DatePicker
            label="End Date"
            value={formData.end_date}
            onChange={(value) => setFormData({ ...formData, end_date: value })}
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Budget"
            type="number"
            step="0.01"
            value={formData.budget}
            onChange={(e) => setFormData({ ...formData, budget: e.target.value })}
            placeholder="0.00"
          />

          <Select
            label="Status *"
            options={statusOptions}
            value={formData.status}
            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
            error={errors.status}
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
            {project ? 'Update' : 'Create'}
          </Button>
        </div>
      </form>
    </Modal>
  )
}

export default ProjectFormModal
