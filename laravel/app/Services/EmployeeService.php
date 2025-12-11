<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function getFilteredEmployees(array $filters = [])
    {
        $query = Employee::query()->with(['transactions', 'project']);
        
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        if (isset($filters['position'])) {
            $query->where('position', $filters['position']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (isset($filters['financial_year'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('employee_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->paginate($filters['size'] ?? 15);
    }

    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::create($data);
            return $employee->fresh(['transactions', 'project']);
        });
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($data);
            return $employee->fresh(['transactions', 'project']);
        });
    }

    public function getEmployeeSalaryHistory(Employee $employee): array
    {
        $totalPaid = $employee->total_paid;
        $outstanding = $employee->outstanding;
        $recentPayments = $employee->transactions()
            ->where('transaction_type', 'credit')
            ->latest()
            ->limit(10)
            ->get();
            
        return [
            'employee' => $employee,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'recent_payments' => $recentPayments,
        ];
    }

    public function getSalaryReport(array $filters = [])
    {
        $query = Employee::with(['transactions' => function ($q) use ($filters) {
            if (isset($filters['financial_year'])) {
                $q->where('financial_year', $filters['financial_year']);
            }
            if (isset($filters['start_date'])) {
                $q->where('date', '>=', $filters['start_date']);
            }
            if (isset($filters['end_date'])) {
                $q->where('date', '<=', $filters['end_date']);
            }
        }, 'deductions' => function ($q) use ($filters) {
            if (isset($filters['financial_year'])) {
                $q->where('financial_year', $filters['financial_year']);
            }
            if (isset($filters['start_date'])) {
                $q->where('date', '>=', $filters['start_date']);
            }
            if (isset($filters['end_date'])) {
                $q->where('date', '<=', $filters['end_date']);
            }
        }]);
        
        return $query->get()->map(function ($employee) use ($filters) {
            $totalPaid = $employee->transactions()
                ->where('transaction_type', 'credit')
                ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                    $q->where('financial_year', $filters['financial_year']);
                })
                ->when(isset($filters['start_date']), function ($q) use ($filters) {
                    $q->where('date', '>=', $filters['start_date']);
                })
                ->when(isset($filters['end_date']), function ($q) use ($filters) {
                    $q->where('date', '<=', $filters['end_date']);
                })
                ->sum('amount');
                
            $totalDeductions = $employee->deductions()
                ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                    $q->where('financial_year', $filters['financial_year']);
                })
                ->when(isset($filters['start_date']), function ($q) use ($filters) {
                    $q->where('date', '>=', $filters['start_date']);
                })
                ->when(isset($filters['end_date']), function ($q) use ($filters) {
                    $q->where('date', '<=', $filters['end_date']);
                })
                ->sum('amount');
                
            return [
                'employee' => $employee,
                'total_paid' => $totalPaid,
                'total_deductions' => $totalDeductions,
                'net_salary' => $totalPaid - $totalDeductions,
                'outstanding' => $employee->salary ? ($employee->salary * 12) - $totalPaid : 0,
            ];
        });
    }
}