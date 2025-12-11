<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardSummary(array $filters = [])
    {
        $accountsQuery = Account::query();
        $projectsQuery = Project::query();
        $employeesQuery = Employee::query();
        $transactionsQuery = Transaction::query();

        // Apply date filters if provided
        if (isset($filters['financial_year'])) {
            $accountsQuery->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
            $projectsQuery->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
            $employeesQuery->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
            $transactionsQuery->where('financial_year', $filters['financial_year']);
        }

        if (isset($filters['start_date'])) {
            $transactionsQuery->where('date', '>=', $filters['start_date']);
            $projectsQuery->where('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $transactionsQuery->where('date', '<=', $filters['end_date']);
            $projectsQuery->where('end_date', '<=', $filters['end_date']);
        }

        // Calculate financial totals
        $totalAssets = Account::where('type', 'asset')
            ->withSum('transactions', function ($q) use ($filters) {
                $this->applyTransactionFilters($q, $filters);
            })
            ->sum('transactions_sum_amount');
            
        $totalLiabilities = Account::where('type', 'liability')
            ->withSum('transactions', function ($q) use ($filters) {
                $this->applyTransactionFilters($q, $filters);
            })
            ->sum('transactions_sum_amount');
            
        $totalEquity = Account::where('type', 'equity')
            ->withSum('transactions', function ($q) use ($filters) {
                $this->applyTransactionFilters($q, $filters);
            })
            ->sum('transactions_sum_amount');
            
        $totalIncome = Transaction::where('transaction_type', 'credit')
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
            
        $totalExpenses = Transaction::where('transaction_type', 'debit')
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

        // Get recent transactions
        $recentTransactions = Transaction::with(['account', 'project', 'employee'])
            ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            })
            ->when(isset($filters['start_date']), function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            })
            ->when(isset($filters['end_date']), function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get project summaries
        $projectSummaries = $this->getProjectSummaries($filters);

        return [
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalIncome - $totalExpenses,
            'active_projects' => $projectsQuery->where('status', 'active')->count(),
            'active_employees' => $employeesQuery->where('is_active', true)->count(),
            'recent_transactions' => $recentTransactions,
            'project_summaries' => $projectSummaries,
        ];
    }

    private function getProjectSummaries(array $filters = [])
    {
        $query = Project::with(['transactions'])
            ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                $q->whereHas('transactions', function ($q) use ($filters) {
                    $q->where('financial_year', $filters['financial_year']);
                });
            });

        return $query->get()->map(function ($project) use ($filters) {
            $projectIncome = $project->transactions()
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

            $projectExpense = $project->transactions()
                ->where('transaction_type', 'debit')
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
                'project' => $project,
                'total_income' => $projectIncome,
                'total_expense' => $projectExpense,
                'balance' => $projectIncome - $projectExpense,
            ];
        });
    }

    private function applyTransactionFilters($query, array $filters = [])
    {
        return $query->when(isset($filters['financial_year']), function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            })
            ->when(isset($filters['start_date']), function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            })
            ->when(isset($filters['end_date']), function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            });
    }

    public function getFinancialYearReport(string $financialYear)
    {
        $summary = $this->getDashboardSummary(['financial_year' => $financialYear]);
        
        // Add year-specific calculations
        $accountsByType = Account::with(['transactions' => function ($q) use ($financialYear) {
            $q->where('financial_year', $financialYear);
        }])
        ->get()
        ->groupBy('type')
        ->map(function ($accounts) {
            return $accounts->sum(function ($account) {
                return $account->balance;
            });
        });

        return [
            'financial_year' => $financialYear,
            'summary' => $summary,
            'accounts_by_type' => $accountsByType,
            'profit_loss' => [
                'total_income' => $summary['total_income'],
                'total_expenses' => $summary['total_expenses'],
                'net_profit' => $summary['net_income'],
                'profit_margin' => $summary['total_income'] > 0 ? 
                    ($summary['net_income'] / $summary['total_income']) * 100 : 0,
            ],
        ];
    }

    public function getProjectReport(array $filters = [])
    {
        return $this->getProjectSummaries($filters);
    }

    public function getEmployeeSalaryReport(array $filters = [])
    {
        $employees = Employee::with(['transactions' => function ($q) use ($filters) {
            if (isset($filters['financial_year'])) {
                $q->where('financial_year', $filters['financial_year']);
            }
            if (isset($filters['start_date'])) {
                $q->where('date', '>=', $filters['start_date']);
            }
            if (isset($filters['end_date'])) {
                $q->where('date', '<=', $filters['end_date']);
            }
        }])->get();

        return $employees->map(function ($employee) use ($filters) {
            $salaryPayments = $employee->transactions()
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
                ->get();

            $totalPaid = $salaryPayments->sum('amount');
            $expectedSalary = $employee->salary ? $employee->salary * 12 : 0;
            $outstanding = $expectedSalary - $totalPaid;

            return [
                'employee' => $employee,
                'total_paid' => $totalPaid,
                'outstanding' => $outstanding,
                'recent_payments' => $salaryPayments->take(5),
                'salary_progress' => $expectedSalary > 0 ? ($totalPaid / $expectedSalary) * 100 : 0,
            ];
        });
    }
}