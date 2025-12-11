<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProjectFinanceService
{
    /**
     * Aggregate income and expense for projects
     */
    public function aggregateProjectFinance(array $filters = []): Collection
    {
        $query = Project::query()->with(['transactions', 'employees', 'employees.deductions']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['financial_year'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
        }
        
        if (isset($filters['start_date'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            });
        }
        
        if (isset($filters['end_date'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            });
        }
        
        if (isset($filters['client_name'])) {
            $query->where('client_name', 'like', '%' . $filters['client_name'] . '%');
        }
        
        return $query->get()->map(function ($project) use ($filters) {
            return $this->calculateProjectFinance($project, $filters);
        });
    }
    
    /**
     * Get outstanding credit card balances by project
     */
    public function getOutstandingCreditCardBalances(array $filters = []): Collection
    {
        $query = Project::query()->with(['transactions' => function ($q) {
            $q->where('category', 'like', '%credit%')
              ->where('transaction_type', 'debit'); // Credit card charges are debits
        }]);
        
        // Apply filters
        if (isset($filters['financial_year'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
        }
        
        if (isset($filters['start_date'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            });
        }
        
        if (isset($filters['end_date'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            });
        }
        
        return $query->get()->map(function ($project) use ($filters) {
            $creditCardTransactions = $project->transactions()
                                            ->where('category', 'like', '%credit%')
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
            
            $totalCreditCardExpenses = $creditCardTransactions->sum('amount');
            
            // Calculate outstanding balance (assuming credit limit and payments tracking)
            $creditLimit = $this->getProjectCreditLimit($project);
            $paymentsMade = $this->getCreditCardPaymentsForProject($project, $filters);
            
            return [
                'project' => $project,
                'total_credit_card_expenses' => $totalCreditCardExpenses,
                'credit_limit' => $creditLimit,
                'payments_made' => $paymentsMade,
                'outstanding_balance' => max(0, $totalCreditCardExpenses - $paymentsMade),
                'credit_available' => max(0, $creditLimit - ($totalCreditCardExpenses - $paymentsMade)),
                'transaction_count' => $creditCardTransactions->count(),
                'recent_transactions' => $creditCardTransactions->sortByDesc('date')->take(10),
            ];
        });
    }
    
    /**
     * Get deductions by project
     */
    public function getDeductionsByProject(array $filters = []): Collection
    {
        $query = Project::query()->with(['employees.deductions']);
        
        // Apply filters
        if (isset($filters['financial_year'])) {
            $query->whereHas('employees.deductions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
        }
        
        if (isset($filters['start_date'])) {
            $query->whereHas('employees.deductions', function ($q) use ($filters) {
                $q->where('date', '>=', $filters['start_date']);
            });
        }
        
        if (isset($filters['end_date'])) {
            $query->whereHas('employees.deductions', function ($q) use ($filters) {
                $q->where('date', '<=', $filters['end_date']);
            });
        }
        
        if (isset($filters['deduction_type'])) {
            $query->whereHas('employees.deductions', function ($q) use ($filters) {
                $q->where('deduction_type', $filters['deduction_type']);
            });
        }
        
        return $query->get()->map(function ($project) use ($filters) {
            return $this->calculateProjectDeductions($project, $filters);
        });
    }
    
    /**
     * Get comprehensive project finance report
     */
    public function getComprehensiveProjectFinanceReport(array $filters = []): array
    {
        $projects = $this->aggregateProjectFinance($filters);
        $creditCardBalances = $this->getOutstandingCreditCardBalances($filters);
        $deductionsByProject = $this->getDeductionsByProject($filters);
        
        // Combine data
        $projectsWithFullData = $projects->map(function ($projectData) use ($creditCardBalances, $deductionsByProject) {
            $creditCardData = $creditCardBalances->where('project.id', $projectData['project']->id)->first();
            $deductionData = $deductionsByProject->where('project.id', $projectData['project']->id)->first();
            
            return array_merge($projectData, [
                'credit_card_info' => $creditCardData ?: [
                    'total_credit_card_expenses' => 0,
                    'outstanding_balance' => 0,
                    'credit_available' => 0,
                ],
                'deduction_info' => $deductionData ?: [
                    'total_deductions' => 0,
                    'deduction_count' => 0,
                ],
            ]);
        });
        
        // Calculate totals
        $totals = [
            'total_income' => $projectsWithFullData->sum('total_income'),
            'total_expenses' => $projectsWithFullData->sum('total_expenses'),
            'total_net_profit' => $projectsWithFullData->sum('net_profit'),
            'total_credit_card_outstanding' => $projectsWithFullData->sum(function ($p) {
                return $p['credit_card_info']['outstanding_balance'] ?? 0;
            }),
            'total_project_deductions' => $projectsWithFullData->sum(function ($p) {
                return $p['deduction_info']['total_deductions'] ?? 0;
            }),
            'project_count' => $projectsWithFullData->count(),
        ];
        
        return [
            'summary' => $totals,
            'projects' => $projectsWithFullData,
            'filters_applied' => $filters,
        ];
    }
    
    /**
     * Get project finance trends over time
     */
    public function getProjectFinanceTrends(string $projectCode, string $period = 'monthly'): array
    {
        $project = Project::where('code', $projectCode)->firstOrFail();
        
        switch ($period) {
            case 'daily':
                return $this->getDailyTrends($project);
            case 'weekly':
                return $this->getWeeklyTrends($project);
            case 'monthly':
            default:
                return $this->getMonthlyTrends($project);
        }
    }
    
    /**
     * Calculate financial metrics for a project
     */
    public function calculateProjectFinancialMetrics(\App\Models\Project $project): array
    {
        $transactions = $project->transactions;
        $employees = $project->employees;
        
        // Basic metrics
        $totalIncome = $transactions->where('transaction_type', 'credit')->sum('amount');
        $totalExpenses = $transactions->where('transaction_type', 'debit')->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;
        $profitMargin = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;
        
        // Employee costs
        $employeeSalaryCosts = $employees->reduce(function ($total, $employee) {
            return $total + ($employee->salary * 12); // Annual salary
        }, 0);
        
        // Credit card expenses
        $creditCardExpenses = $transactions->where('category', 'like', '%credit%')->sum('amount');
        
        // ROI calculation
        $roi = $this->calculateProjectROI($project, $netProfit);
        
        return [
            'project' => $project,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 2),
            'employee_costs' => $employeeSalaryCosts,
            'credit_card_expenses' => $creditCardExpenses,
            'return_on_investment' => $roi,
            'budget_utilization' => $project->budget ? ($totalExpenses / $project->budget) * 100 : 0,
        ];
    }
    
    /**
     * Calculate project finance data with applied filters
     */
    private function calculateProjectFinance(Project $project, array $filters): array
    {
        $transactions = $project->transactions()
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
        
        $totalIncome = $transactions->where('transaction_type', 'credit')->sum('amount');
        $totalExpenses = $transactions->where('transaction_type', 'debit')->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;
        
        // Category breakdown
        $categoryBreakdown = $transactions->groupBy('category')
                                         ->map(function ($categoryTransactions) {
                                             return [
                                                 'income' => $categoryTransactions->where('transaction_type', 'credit')->sum('amount'),
                                                 'expenses' => $categoryTransactions->where('transaction_type', 'debit')->sum('amount'),
                                                 'net' => $categoryTransactions->where('transaction_type', 'credit')->sum('amount') - 
                                                         $categoryTransactions->where('transaction_type', 'debit')->sum('amount'),
                                                 'transaction_count' => $categoryTransactions->count(),
                                             ];
                                         });
        
        return [
            'project' => $project,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0,
            'budget' => $project->budget,
            'budget_variance' => $project->budget ? $project->budget - $totalExpenses : null,
            'budget_utilization' => $project->budget && $project->budget > 0 ? round(($totalExpenses / $project->budget) * 100, 2) : 0,
            'transaction_count' => $transactions->count(),
            'category_breakdown' => $categoryBreakdown,
        ];
    }
    
    /**
     * Calculate deductions for a project
     */
    private function calculateProjectDeductions(Project $project, array $filters): array
    {
        $employees = $project->employees;
        $allDeductions = collect();
        
        foreach ($employees as $employee) {
            $employeeDeductions = $employee->deductions()
                                          ->when(isset($filters['financial_year']), function ($q) use ($filters) {
                                              $q->where('financial_year', $filters['financial_year']);
                                          })
                                          ->when(isset($filters['start_date']), function ($q) use ($filters) {
                                              $q->where('date', '>=', $filters['start_date']);
                                          })
                                          ->when(isset($filters['end_date']), function ($q) use ($filters) {
                                              $q->where('date', '<=', $filters['end_date']);
                                          })
                                          ->when(isset($filters['deduction_type']), function ($q) use ($filters) {
                                              $q->where('deduction_type', $filters['deduction_type']);
                                          })
                                          ->get();
            
            $allDeductions = $allDeductions->concat($employeeDeductions);
        }
        
        $totalDeductions = $allDeductions->sum('amount');
        $deductionTypeBreakdown = $allDeductions->groupBy('deduction_type')
                                               ->map(function ($deductions) {
                                                   return [
                                                       'total_amount' => $deductions->sum('amount'),
                                                       'count' => $deductions->count(),
                                                   ];
                                               });
        
        return [
            'project' => $project,
            'total_deductions' => $totalDeductions,
            'deduction_count' => $allDeductions->count(),
            'employee_count' => $employees->count(),
            'deduction_type_breakdown' => $deductionTypeBreakdown,
            'recurring_deductions' => $allDeductions->where('is_recurring', true)->sum('amount'),
            'deductions' => $allDeductions,
        ];
    }
    
    /**
     * Get project credit limit (customize based on your business logic)
     */
    private function getProjectCreditLimit(Project $project): float
    {
        // This is a placeholder. In a real system, this would come from project settings
        // or a separate project_credit_limits table
        return $project->budget ? $project->budget * 0.5 : 0; // 50% of budget as credit limit
    }
    
    /**
     * Get credit card payments for a project
     */
    private function getCreditCardPaymentsForProject(Project $project, array $filters): float
    {
        // Credit card payments would be transactions with negative amounts
        // or transactions categorized as credit card payments
        return $project->transactions()
                      ->where('category', 'like', '%credit%payment%')
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
    }
    
    /**
     * Get daily trends for a project
     */
    private function getDailyTrends(Project $project): array
    {
        // Implementation for daily trends
        // This would aggregate transactions by day
        return [];
    }
    
    /**
     * Get weekly trends for a project
     */
    private function getWeeklyTrends(Project $project): array
    {
        // Implementation for weekly trends
        // This would aggregate transactions by week
        return [];
    }
    
    /**
     * Get monthly trends for a project
     */
    private function getMonthlyTrends(Project $project): array
    {
        // Implementation for monthly trends
        // This would aggregate transactions by month
        return [];
    }
    
    /**
     * Calculate ROI for a project
     */
    private function calculateProjectROI(Project $project, float $netProfit): float
    {
        // Simple ROI calculation
        // In a real system, you'd calculate total investment (costs, time, resources)
        $investment = $project->budget ?: 1; // Avoid division by zero
        
        return $investment > 0 ? (($netProfit - $investment) / $investment) * 100 : 0;
    }
}