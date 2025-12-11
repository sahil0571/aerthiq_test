<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SalaryPaymentService
{
    /**
     * Record salary transaction for an employee
     */
    public function recordSalaryPayment(Employee $employee, array $paymentData): Transaction
    {
        return DB::transaction(function () use ($employee, $paymentData) {
            // Create salary transaction
            $transactionData = [
                'date' => $paymentData['date'],
                'description' => 'Salary Payment - ' . $employee->full_name,
                'amount' => $paymentData['amount'],
                'transaction_type' => 'credit',
                'account_id' => $paymentData['account_id'],
                'employee_id' => $employee->id,
                'financial_year' => $paymentData['financial_year'],
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
            ];
            
            $transaction = Transaction::create($transactionData);
            
            // Link deductions if provided
            if (isset($paymentData['deductions']) && is_array($paymentData['deductions'])) {
                foreach ($paymentData['deductions'] as $deductionInfo) {
                    $this->applyDeductionToTransaction($transaction, $deductionInfo);
                }
            }
            
            // Create deduction records for salary payment deductions
            if (isset($paymentData['create_deductions']) && is_array($paymentData['create_deductions'])) {
                foreach ($paymentData['create_deductions'] as $deductionData) {
                    $this->createSalaryDeduction($employee, $deductionData, $transaction);
                }
            }
            
            return $transaction->fresh(['employee', 'deductions']);
        });
    }
    
    /**
     * Get salary history summary by financial year
     */
    public function getSalaryHistoryByFinancialYear(string $financialYear, array $filters = []): Collection
    {
        $query = Employee::query()->with(['transactions' => function ($q) use ($financialYear) {
            $q->where('financial_year', $financialYear)
              ->where('transaction_type', 'credit');
        }, 'deductions' => function ($q) use ($financialYear) {
            $q->where('financial_year', $financialYear);
        }]);
        
        // Apply filters
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        return $query->get()->map(function ($employee) use ($financialYear) {
            $salaryTransactions = $employee->transactions()
                                          ->where('financial_year', $financialYear)
                                          ->where('transaction_type', 'credit')
                                          ->get();
            
            $totalPaid = $salaryTransactions->sum('amount');
            $monthlyBreakdown = $this->getMonthlySalaryBreakdown($salaryTransactions);
            $totalDeductions = $employee->deductions()
                                       ->where('financial_year', $financialYear)
                                       ->sum('amount');
            
            return [
                'employee' => $employee,
                'financial_year' => $financialYear,
                'total_paid' => $totalPaid,
                'total_deductions' => $totalDeductions,
                'net_salary' => $totalPaid - $totalDeductions,
                'monthly_breakdown' => $monthlyBreakdown,
                'payment_count' => $salaryTransactions->count(),
                'outstanding' => $this->calculateOutstandingSalary($employee, $financialYear),
            ];
        });
    }
    
    /**
     * Get salary history summary by month
     */
    public function getSalaryHistoryByMonth(int $year, int $month, array $filters = []): Collection
    {
        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $query = Employee::query()->with(['transactions' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate])
              ->where('transaction_type', 'credit');
        }, 'deductions' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        }]);
        
        // Apply filters
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        return $query->get()->map(function ($employee) use ($startDate, $endDate) {
            $salaryTransactions = $employee->transactions()
                                          ->whereBetween('date', [$startDate, $endDate])
                                          ->where('transaction_type', 'credit')
                                          ->get();
            
            $totalPaid = $salaryTransactions->sum('amount');
            $totalDeductions = $employee->deductions()
                                       ->whereBetween('date', [$startDate, $endDate])
                                       ->sum('amount');
            
            return [
                'employee' => $employee,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'month' => $month,
                    'year' => $year,
                ],
                'total_paid' => $totalPaid,
                'total_deductions' => $totalDeductions,
                'net_salary' => $totalPaid - $totalDeductions,
                'payment_count' => $salaryTransactions->count(),
                'transactions' => $salaryTransactions,
            ];
        });
    }
    
    /**
     * Get monthly salary summary across all employees
     */
    public function getMonthlySalarySummary(int $year, int $month): array
    {
        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $totalPaid = Transaction::whereBetween('date', [$startDate, $endDate])
                               ->where('transaction_type', 'credit')
                               ->whereHas('employee')
                               ->sum('amount');
        
        $totalDeductions = Deduction::whereBetween('date', [$startDate, $endDate])
                                   ->sum('amount');
        
        $paymentCount = Transaction::whereBetween('date', [$startDate, $endDate])
                                  ->where('transaction_type', 'credit')
                                  ->whereHas('employee')
                                  ->count();
        
        $employeeCount = Transaction::whereBetween('date', [$startDate, $endDate])
                                   ->where('transaction_type', 'credit')
                                   ->whereHas('employee')
                                   ->distinct('employee_id')
                                   ->count('employee_id');
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'month' => $month,
                'year' => $year,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            ],
            'total_paid' => $totalPaid,
            'total_deductions' => $totalDeductions,
            'net_salary' => $totalPaid - $totalDeductions,
            'payment_count' => $paymentCount,
            'employee_count' => $employeeCount,
            'average_salary' => $employeeCount > 0 ? round($totalPaid / $employeeCount, 2) : 0,
        ];
    }
    
    /**
     * Get financial year salary summary
     */
    public function getFinancialYearSalarySummary(string $financialYear): array
    {
        $totalPaid = Transaction::where('financial_year', $financialYear)
                               ->where('transaction_type', 'credit')
                               ->whereHas('employee')
                               ->sum('amount');
        
        $totalDeductions = Deduction::where('financial_year', $financialYear)
                                   ->sum('amount');
        
        $paymentCount = Transaction::where('financial_year', $financialYear)
                                  ->where('transaction_type', 'credit')
                                  ->whereHas('employee')
                                  ->count();
        
        $employeeCount = Transaction::where('financial_year', $financialYear)
                                   ->where('transaction_type', 'credit')
                                   ->whereHas('employee')
                                   ->distinct('employee_id')
                                   ->count('employee_id');
        
        return [
            'financial_year' => $financialYear,
            'total_paid' => $totalPaid,
            'total_deductions' => $totalDeductions,
            'net_salary' => $totalPaid - $totalDeductions,
            'payment_count' => $paymentCount,
            'employee_count' => $employeeCount,
            'average_salary' => $employeeCount > 0 ? round($totalPaid / $employeeCount, 2) : 0,
        ];
    }
    
    /**
     * Apply deduction to transaction
     */
    private function applyDeductionToTransaction(Transaction $transaction, array $deductionInfo): void
    {
        $deduction = Deduction::find($deductionInfo['deduction_id']);
        $amountApplied = $deductionInfo['amount_applied'] ?? $deduction->amount;
        
        $transaction->deductions()->attach($deduction->id, [
            'amount_applied' => $amountApplied
        ]);
    }
    
    /**
     * Create salary deduction record
     */
    private function createSalaryDeduction(Employee $employee, array $deductionData, Transaction $transaction): void
    {
        $deduction = Deduction::create([
            'employee_id' => $employee->id,
            'amount' => $deductionData['amount'],
            'description' => $deductionData['description'],
            'date' => $deductionData['date'],
            'deduction_type' => $deductionData['deduction_type'],
            'is_recurring' => $deductionData['is_recurring'] ?? false,
            'monthly_deduction' => $deductionData['monthly_deduction'] ?? null,
            'financial_year' => $transaction->financial_year,
        ]);
        
        // Link deduction to transaction
        $transaction->deductions()->attach($deduction->id, [
            'amount_applied' => $deductionData['amount']
        ]);
    }
    
    /**
     * Get monthly salary breakdown for an employee
     */
    private function getMonthlySalaryBreakdown(Collection $transactions): array
    {
        $breakdown = [];
        
        foreach ($transactions as $transaction) {
            $month = $transaction->date->format('Y-m');
            
            if (!isset($breakdown[$month])) {
                $breakdown[$month] = [
                    'month' => $month,
                    'month_name' => $transaction->date->format('F Y'),
                    'total_amount' => 0,
                    'transaction_count' => 0,
                    'transactions' => [],
                ];
            }
            
            $breakdown[$month]['total_amount'] += $transaction->amount;
            $breakdown[$month]['transaction_count']++;
            $breakdown[$month]['transactions'][] = $transaction;
        }
        
        return array_values($breakdown);
    }
    
    /**
     * Calculate outstanding salary for employee in a financial year
     */
    private function calculateOutstandingSalary(Employee $employee, string $financialYear): float
    {
        if (!$employee->salary) {
            return 0;
        }
        
        // Calculate expected salary for the period
        $yearParts = explode('-', $financialYear);
        $startYear = intval($yearParts[0]);
        $endYear = intval($yearParts[1]);
        
        $fyStart = date('Y-m-d', mktime(0, 0, 0, 4, 1, $startYear)); // April 1st
        $fyEnd = date('Y-m-d', mktime(0, 0, 0, 3, 31, $endYear)); // March 31st
        
        // Calculate months worked in the financial year
        $hireDate = $employee->hire_date;
        if ($hireDate && $hireDate->greaterThan($fyEnd)) {
            return 0; // Not hired yet in this FY
        }
        
        $actualStart = $hireDate && $hireDate->greaterThan($fyStart) ? $hireDate : $fyStart;
        $monthsWorked = $actualStart->diffInMonths($fyEnd) + 1;
        
        $expectedSalary = $employee->salary * $monthsWorked;
        
        // Get paid amount for this FY
        $totalPaid = $employee->transactions()
                              ->where('financial_year', $financialYear)
                              ->where('transaction_type', 'credit')
                              ->sum('amount');
        
        return round(max(0, $expectedSalary - $totalPaid), 2);
    }
}