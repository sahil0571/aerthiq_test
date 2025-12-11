<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class FYFilteredReportsTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_fy_filtered_dashboard_summary()
    {
        // Create test data for FY2024
        $account = Account::factory()->create(['type' => 'income']);
        $transaction2024 = Transaction::factory()->create([
            'account_id' => $account->id,
            'financial_year' => 'FY2024',
            'amount' => 10000.00,
            'transaction_type' => 'credit',
            'date' => now()->subMonths(3),
        ]);

        // Create test data for FY2023
        $transaction2023 = Transaction::factory()->create([
            'account_id' => $account->id,
            'financial_year' => 'FY2023',
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => now()->subMonths(15),
        ]);

        // Test dashboard summary with FY2024 filter
        $response = $this->getJson('/api/dashboard/summary?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals(10000.00, $data['total_income']);
        $this->assertEquals(0.00, $data['total_expenses']);
        $this->assertEquals(10000.00, $data['net_income']);
    }

    public function test_fy_filtered_project_report()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create accounts for income and expenses
        $incomeAccount = Account::factory()->create(['type' => 'income']);
        $expenseAccount = Account::factory()->create(['type' => 'expense']);

        // Create transactions for different financial years
        Transaction::factory()->create([
            'account_id' => $incomeAccount->id,
            'project_id' => $project->id,
            'financial_year' => 'FY2024',
            'amount' => 15000.00,
            'transaction_type' => 'credit',
        ]);

        Transaction::factory()->create([
            'account_id' => $expenseAccount->id,
            'project_id' => $project->id,
            'financial_year' => 'FY2024',
            'amount' => 8000.00,
            'transaction_type' => 'debit',
        ]);

        Transaction::factory()->create([
            'account_id' => $incomeAccount->id,
            'project_id' => $project->id,
            'financial_year' => 'FY2023',
            'amount' => 10000.00,
            'transaction_type' => 'credit',
        ]);

        // Test project report with FY2024 filter
        $response = $this->getJson('/api/reports/projects?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(1, $data['items']);
        $projectSummary = $data['items'][0];
        $this->assertEquals(15000.00, $projectSummary['total_income']);
        $this->assertEquals(8000.00, $projectSummary['total_expense']);
        $this->assertEquals(7000.00, $projectSummary['balance']);
    }

    public function test_date_range_filtered_reports()
    {
        // Create an account
        $account = Account::factory()->create(['type' => 'income']);

        // Create transactions with different dates
        $startDate = now()->subMonths(6)->toDateString();
        $endDate = now()->subMonths(3)->toDateString();
        $outsideDate = now()->subMonths(8)->toDateString();

        Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => $startDate,
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 3000.00,
            'transaction_type' => 'credit',
            'date' => $endDate,
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 2000.00,
            'transaction_type' => 'credit',
            'date' => $outsideDate,
        ]);

        // Test dashboard summary with date range filter
        $response = $this->getJson("/api/dashboard/summary?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals(8000.00, $data['total_income']); // 5000 + 3000 only
    }

    public function test_employee_salary_report_with_fy_filter()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Create an account for salary payments
        $account = Account::factory()->create(['type' => 'asset']);

        // Create salary transactions for different financial years
        Transaction::factory()->create([
            'account_id' => $account->id,
            'employee_id' => $employee->id,
            'financial_year' => 'FY2024',
            'amount' => 5000.00,
            'transaction_type' => 'credit',
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'employee_id' => $employee->id,
            'financial_year' => 'FY2023',
            'amount' => 4500.00,
            'transaction_type' => 'credit',
        ]);

        // Test employee salary report with FY2024 filter
        $response = $this->getJson('/api/reports/employees/salary?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(1, $data['items']);
        $salaryData = $data['items'][0];
        $this->assertEquals(5000.00, $salaryData['total_paid']);
    }
}