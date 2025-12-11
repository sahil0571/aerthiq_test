import { useState } from 'react'
import { Filter, X } from 'lucide-react'
import Input from '../ui/Input'
import DatePicker from '../ui/DatePicker'
import Select from '../ui/Select'
import Button from '../ui/Button'
import { useProjects } from '@/hooks/useProjects'
import { useEmployees } from '@/hooks/useEmployees'
import type { FilterParams } from '@/types'

interface FilterBarProps {
  onFilter: (filters: FilterParams) => void
  showProject?: boolean
  showEmployee?: boolean
  showDateRange?: boolean
  showFinancialYear?: boolean
  showStatus?: boolean
  statusOptions?: { value: string; label: string }[]
}

const FilterBar = ({
  onFilter,
  showProject = false,
  showEmployee = false,
  showDateRange = true,
  showFinancialYear = true,
  showStatus = false,
  statusOptions = [],
}: FilterBarProps) => {
  const [isExpanded, setIsExpanded] = useState(false)
  const [filters, setFilters] = useState<FilterParams>({})

  const { data: projectsData } = useProjects({ size: 1000 })
  const { data: employeesData } = useEmployees({ size: 1000 })

  const handleFilterChange = (key: keyof FilterParams, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value || undefined }))
  }

  const handleApplyFilters = () => {
    onFilter(filters)
  }

  const handleClearFilters = () => {
    setFilters({})
    onFilter({})
  }

  const currentYear = new Date().getFullYear()
  const financialYears = Array.from({ length: 5 }, (_, i) => {
    const year = currentYear - i
    return { value: `FY${year}`, label: `FY ${year}` }
  })

  return (
    <div className="card p-4 mb-6">
      <div className="flex items-center justify-between">
        <button
          onClick={() => setIsExpanded(!isExpanded)}
          className="flex items-center text-gray-700 hover:text-gray-900 font-medium"
        >
          <Filter className="w-4 h-4 mr-2" />
          Filters
        </button>
        {Object.keys(filters).length > 0 && (
          <button
            onClick={handleClearFilters}
            className="text-sm text-gray-500 hover:text-gray-700 flex items-center"
          >
            <X className="w-4 h-4 mr-1" />
            Clear
          </button>
        )}
      </div>

      {isExpanded && (
        <div className="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {showFinancialYear && (
            <Select
              label="Financial Year"
              options={financialYears}
              value={filters.financial_year || ''}
              onChange={(e) => handleFilterChange('financial_year', e.target.value)}
            />
          )}

          {showDateRange && (
            <>
              <DatePicker
                label="Start Date"
                value={filters.start_date || ''}
                onChange={(value) => handleFilterChange('start_date', value)}
              />
              <DatePicker
                label="End Date"
                value={filters.end_date || ''}
                onChange={(value) => handleFilterChange('end_date', value)}
              />
            </>
          )}

          {showProject && projectsData?.items && (
            <Select
              label="Project"
              options={projectsData.items.map(p => ({ value: p.id, label: p.name }))}
              value={filters.project_id || ''}
              onChange={(e) => handleFilterChange('project_id', e.target.value)}
            />
          )}

          {showEmployee && employeesData?.items && (
            <Select
              label="Employee"
              options={employeesData.items.map(e => ({
                value: e.id,
                label: `${e.first_name} ${e.last_name}`,
              }))}
              value={filters.employee_id || ''}
              onChange={(e) => handleFilterChange('employee_id', e.target.value)}
            />
          )}

          {showStatus && statusOptions.length > 0 && (
            <Select
              label="Status"
              options={statusOptions}
              value={filters.status || ''}
              onChange={(e) => handleFilterChange('status', e.target.value)}
            />
          )}

          <Input
            label="Search"
            placeholder="Search..."
            value={filters.search || ''}
            onChange={(e) => handleFilterChange('search', e.target.value)}
          />

          <div className="flex items-end">
            <Button onClick={handleApplyFilters} className="w-full">
              Apply Filters
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}

export default FilterBar
