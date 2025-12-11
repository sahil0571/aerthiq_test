<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date'
        ]);
        
        $summary = $this->reportService->getDashboardSummary($filters);
        
        return response()->json($summary);
    }

    public function financialYearReport(Request $request, $financialYear)
    {
        $report = $this->reportService->getFinancialYearReport($financialYear);
        
        return response()->json($report);
    }

    public function projectReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date', 'project_id'
        ]);
        
        $report = $this->reportService->getProjectReport($filters);
        
        return response()->json([
            'items' => $report,
            'total' => count($report),
        ]);
    }

    public function employeeSalaryReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date'
        ]);
        
        $report = $this->reportService->getEmployeeSalaryReport($filters);
        
        return response()->json([
            'items' => $report,
            'total' => count($report),
        ]);
    }

    public function profitLossReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date'
        ]);
        
        $summary = $this->reportService->getDashboardSummary($filters);
        
        return response()->json([
            'total_income' => $summary['total_income'],
            'total_expenses' => $summary['total_expenses'],
            'net_income' => $summary['net_income'],
            'profit_margin' => $summary['total_income'] > 0 ? 
                ($summary['net_income'] / $summary['total_income']) * 100 : 0,
            'period' => [
                'financial_year' => $filters['financial_year'] ?? null,
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
            ],
        ]);
    }

    public function balanceSheetReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'end_date'
        ]);
        
        $summary = $this->reportService->getDashboardSummary($filters);
        
        return response()->json([
            'assets' => [
                'total' => $summary['total_assets'],
                'type' => 'Total Assets',
            ],
            'liabilities' => [
                'total' => $summary['total_liabilities'],
                'type' => 'Total Liabilities',
            ],
            'equity' => [
                'total' => $summary['total_equity'],
                'type' => 'Total Equity',
            ],
            'as_of_date' => $filters['end_date'] ?? now()->toDateString(),
        ]);
    }

    public function projectFinancialSummary(Request $request, $projectId)
    {
        $project = \App\Models\Project::with(['transactions'])->findOrFail($projectId);
        
        $incomeTransactions = $project->transactions()
            ->where('transaction_type', 'credit')
            ->when($request->financial_year, function ($q) use ($request) {
                $q->where('financial_year', $request->financial_year);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->where('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->where('date', '<=', $request->end_date);
            })
            ->get();

        $expenseTransactions = $project->transactions()
            ->where('transaction_type', 'debit')
            ->when($request->financial_year, function ($q) use ($request) {
                $q->where('financial_year', $request->financial_year);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->where('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->where('date', '<=', $request->end_date);
            })
            ->get();

        return response()->json([
            'project' => $project,
            'income_transactions' => $incomeTransactions,
            'expense_transactions' => $expenseTransactions,
            'summary' => [
                'total_income' => $incomeTransactions->sum('amount'),
                'total_expense' => $expenseTransactions->sum('amount'),
                'balance' => $incomeTransactions->sum('amount') - $expenseTransactions->sum('amount'),
                'transaction_count' => $incomeTransactions->count() + $expenseTransactions->count(),
            ],
        ]);
    }
}