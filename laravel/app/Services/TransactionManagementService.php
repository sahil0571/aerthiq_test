<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Deduction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TransactionManagementService
{
    /**
     * Create a transaction with optional deduction linkage
     */
    public function createTransactionWithDeductions(array $transactionData, array $deductionsData = []): Transaction
    {
        return DB::transaction(function () use ($transactionData, $deductionsData) {
            $transaction = Transaction::create($transactionData);
            
            // Link deductions if provided
            foreach ($deductionsData as $deductionData) {
                $this->attachDeductionToTransaction($transaction, $deductionData);
            }
            
            $this->recalculateAffectedBalances($transaction);
            
            return $transaction->fresh(['account', 'project', 'employee', 'deductions']);
        });
    }
    
    /**
     * Update a transaction and handle deduction linkage changes
     */
    public function updateTransactionWithDeductions(Transaction $transaction, array $transactionData, array $deductionsData = []): Transaction
    {
        return DB::transaction(function () use ($transaction, $transactionData, $deductionsData) {
            $oldTransactionData = $transaction->toArray();
            
            $transaction->update($transactionData);
            
            // Update deductions linkage
            $this->syncTransactionDeductions($transaction, $deductionsData);
            
            $this->recalculateAffectedBalances($transaction);
            
            return $transaction->fresh(['account', 'project', 'employee', 'deductions']);
        });
    }
    
    /**
     * Delete a transaction and clean up deductions linkage
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $accountId = $transaction->account_id;
            $projectId = $transaction->project_id;
            $employeeId = $transaction->employee_id;
            
            // Detach all deductions from transaction
            $transaction->deductions()->detach();
            
            $transaction->delete();
            
            // Recalculate affected balances
            $this->recalculateAccountBalance($accountId);
            
            if ($projectId) {
                $this->recalculateProjectBalance($projectId);
            }
            
            if ($employeeId) {
                $this->recalculateEmployeeBalance($employeeId);
            }
            
            return true;
        });
    }
    
    /**
     * Get computed account balance (without persisting)
     */
    public function getComputedAccountBalance(Account $account): float
    {
        $balance = $account->opening_balance ?? 0;
        
        foreach ($account->transactions as $transaction) {
            if ($transaction->transaction_type === 'debit') {
                $balance -= $transaction->amount;
            } else {
                $balance += $transaction->amount;
            }
        }
        
        return round($balance, 2);
    }
    
    /**
     * Get computed project balance (income - expenses)
     */
    public function getComputedProjectBalance(\App\Models\Project $project): float
    {
        $income = $project->transactions()
                         ->where('transaction_type', 'credit')
                         ->sum('amount');
                         
        $expenses = $project->transactions()
                           ->where('transaction_type', 'debit')
                           ->sum('amount');
                           
        return round($income - $expenses, 2);
    }
    
    /**
     * Get computed employee outstanding salary
     */
    public function getComputedEmployeeOutstanding(\App\Models\Employee $employee): float
    {
        if (!$employee->hire_date || !$employee->salary) {
            return 0;
        }
        
        $monthsWorked = $employee->hire_date->diffInMonths(now()) + 1;
        $expectedSalary = $employee->salary * $monthsWorked;
        
        $totalPaid = $employee->transactions()
                             ->where('transaction_type', 'credit')
                             ->sum('amount');
                            
        return round($expectedSalary - $totalPaid, 2);
    }
    
    /**
     * Get transactions by financial year with filters
     */
    public function getTransactionsByFinancialYear(string $financialYear, array $filters = []): Collection
    {
        $query = Transaction::with(['account', 'project', 'employee', 'deductions'])
                           ->where('financial_year', $financialYear);
                           
        // Apply filters
        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        
        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }
        
        return $query->orderBy('date', 'desc')->get();
    }
    
    /**
     * Attach a deduction to a transaction
     */
    private function attachDeductionToTransaction(Transaction $transaction, array $deductionData): void
    {
        $deduction = Deduction::find($deductionData['deduction_id']);
        $amountApplied = $deductionData['amount_applied'] ?? $deduction->amount;
        
        $transaction->deductions()->attach($deduction->id, [
            'amount_applied' => $amountApplied
        ]);
    }
    
    /**
     * Sync transaction deductions
     */
    private function syncTransactionDeductions(Transaction $transaction, array $deductionsData): void
    {
        $syncData = [];
        
        foreach ($deductionsData as $deductionData) {
            $deduction = Deduction::find($deductionData['deduction_id']);
            $amountApplied = $deductionData['amount_applied'] ?? $deduction->amount;
            
            $syncData[$deduction->id] = [
                'amount_applied' => $amountApplied
            ];
        }
        
        $transaction->deductions()->sync($syncData);
    }
    
    /**
     * Recalculate all affected balances after transaction changes
     */
    private function recalculateAffectedBalances(Transaction $transaction): void
    {
        // Recalculate account balance (touch to update computed balance)
        $this->recalculateAccountBalance($transaction->account_id);
        
        // Recalculate project balance if applicable
        if ($transaction->project_id) {
            $this->recalculateProjectBalance($transaction->project_id);
        }
        
        // Recalculate employee balance if applicable
        if ($transaction->employee_id) {
            $this->recalculateEmployeeBalance($transaction->employee_id);
        }
    }
    
    /**
     * Recalculate account balance
     */
    private function recalculateAccountBalance(int $accountId): void
    {
        $account = Account::with('transactions')->find($accountId);
        if ($account) {
            $account->touch(); // Update timestamp to indicate balance recalculation
        }
    }
    
    /**
     * Recalculate project balance
     */
    private function recalculateProjectBalance(int $projectId): void
    {
        $project = \App\Models\Project::with('transactions')->find($projectId);
        if ($project) {
            $project->touch(); // Update timestamp to indicate balance recalculation
        }
    }
    
    /**
     * Recalculate employee outstanding balance
     */
    private function recalculateEmployeeBalance(int $employeeId): void
    {
        $employee = \App\Models\Employee::with(['transactions', 'deductions'])->find($employeeId);
        if ($employee) {
            $employee->touch(); // Update timestamp to indicate balance recalculation
        }
    }
}