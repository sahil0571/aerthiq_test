<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function getFilteredTransactions(array $filters = [])
    {
        $query = Transaction::with(['account', 'project', 'employee']);
        
        if (isset($filters['financial_year'])) {
            $query->where('financial_year', $filters['financial_year']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        
        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }
        
        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('reference', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('notes', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('date', 'desc')->paginate($filters['size'] ?? 15);
    }

    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            
            // Recalculate account balance
            $this->recalculateAccountBalance($transaction->account_id);
            
            // Recalculate project totals if project is associated
            if ($transaction->project_id) {
                $this->recalculateProjectTotals($transaction->project_id);
            }
            
            // Recalculate employee outstanding if employee is associated
            if ($transaction->employee_id) {
                $this->recalculateEmployeeOutstanding($transaction->employee_id);
            }
            
            return $transaction->load(['account', 'project', 'employee']);
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $oldAccountId = $transaction->account_id;
            $oldProjectId = $transaction->project_id;
            $oldEmployeeId = $transaction->employee_id;
            
            $transaction->update($data);
            
            // Recalculate old account balance if account changed
            if ($oldAccountId != $transaction->account_id) {
                $this->recalculateAccountBalance($oldAccountId);
            }
            $this->recalculateAccountBalance($transaction->account_id);
            
            // Recalculate old project totals if project changed
            if ($oldProjectId != $transaction->project_id) {
                if ($oldProjectId) {
                    $this->recalculateProjectTotals($oldProjectId);
                }
                if ($transaction->project_id) {
                    $this->recalculateProjectTotals($transaction->project_id);
                }
            } elseif ($transaction->project_id) {
                $this->recalculateProjectTotals($transaction->project_id);
            }
            
            // Recalculate old employee outstanding if employee changed
            if ($oldEmployeeId != $transaction->employee_id) {
                if ($oldEmployeeId) {
                    $this->recalculateEmployeeOutstanding($oldEmployeeId);
                }
                if ($transaction->employee_id) {
                    $this->recalculateEmployeeOutstanding($transaction->employee_id);
                }
            } elseif ($transaction->employee_id) {
                $this->recalculateEmployeeOutstanding($transaction->employee_id);
            }
            
            return $transaction->fresh(['account', 'project', 'employee']);
        });
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $accountId = $transaction->account_id;
            $projectId = $transaction->project_id;
            $employeeId = $transaction->employee_id;
            
            $transaction->delete();
            
            // Recalculate affected balances
            $this->recalculateAccountBalance($accountId);
            
            if ($projectId) {
                $this->recalculateProjectTotals($projectId);
            }
            
            if ($employeeId) {
                $this->recalculateEmployeeOutstanding($employeeId);
            }
            
            return true;
        });
    }

    private function recalculateAccountBalance(int $accountId): void
    {
        $account = Account::with('transactions')->find($accountId);
        if ($account) {
            $account->touch(); // Update updated_at timestamp
        }
    }

    private function recalculateProjectTotals(int $projectId): void
    {
        $project = Project::find($projectId);
        if ($project) {
            $project->touch(); // Update updated_at timestamp
        }
    }

    private function recalculateEmployeeOutstanding(int $employeeId): void
    {
        $employee = Employee::find($employeeId);
        if ($employee) {
            $employee->touch(); // Update updated_at timestamp
        }
    }
}