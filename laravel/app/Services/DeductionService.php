<?php

namespace App\Services;

use App\Models\Deduction;
use Illuminate\Support\Facades\DB;

class DeductionService
{
    public function getFilteredDeductions(array $filters = [])
    {
        $query = Deduction::query()->with(['employee']);
        
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        
        if (isset($filters['deduction_type'])) {
            $query->where('deduction_type', $filters['deduction_type']);
        }
        
        if (isset($filters['is_recurring'])) {
            $query->where('is_recurring', $filters['is_recurring']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }
        
        if (isset($filters['financial_year'])) {
            $query->where('financial_year', $filters['financial_year']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('employee', function ($employeeQuery) use ($filters) {
                      $employeeQuery->where('first_name', 'like', '%' . $filters['search'] . '%')
                                   ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('date', 'desc')->paginate($filters['size'] ?? 15);
    }

    public function createDeduction(array $data): Deduction
    {
        return DB::transaction(function () use ($data) {
            $deduction = Deduction::create($data);
            return $deduction->fresh(['employee']);
        });
    }

    public function updateDeduction(Deduction $deduction, array $data): Deduction
    {
        return DB::transaction(function () use ($deduction, $data) {
            $deduction->update($data);
            return $deduction->fresh(['employee']);
        });
    }

    public function getEmployeeDeductionsReport(array $filters = [])
    {
        $query = Deduction::with(['employee'])
            ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            })
            ->when(isset($filters['start_date']), function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            })
            ->when(isset($filters['end_date']), function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            });
            
        return $query->get()->groupBy('employee_id')->map(function ($deductions) {
            $employee = $deductions->first()->employee;
            
            return [
                'employee' => $employee,
                'total_deductions' => $deductions->sum('amount'),
                'deduction_count' => $deductions->count(),
                'deductions_by_type' => $deductions->groupBy('deduction_type')->map(function ($typeDeductions) {
                    return [
                        'total' => $typeDeductions->sum('amount'),
                        'count' => $typeDeductions->count(),
                    ];
                }),
            ];
        });
    }
}