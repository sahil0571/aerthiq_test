<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class EmployeeSalaryHistoryTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_employee_salary_history_retrieval()
    {
        // Create an employee
        $employee = Employee::factory()->create([
            'salary' => 5000.00,
            'hire_date' => now()->subMonths(6),
        ]);

        // Create an account for salary payments
        $account = Account::factory()->create(['type' => 'asset']);

        // Create multiple salary transactions over time
        $salaryTransactions = Transaction::factory()->count(3)->create([
            'account_id' => $account->id,
            'employee_id' => $employee->id,
            'transaction_type' => 'credit',
            'financial_year' => 'FY2024',
        ]);

        $totalPaid = $salaryTransactions->sum('amount');

        // Test salary history endpoint
        $response = $this->getJson("/api/employees/{$employee->id}/salary-history");

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals($employee->id, $data['employee']['id']);
        $this->assertEquals($totalPaid, $data['total_paid']);
        $this->assertArrayHasKey('outstanding', $data);
        $this->assertArrayHasKey('recent_payments', $data);
        $this->assertCount(3, $data['recent_payments']);
    }

    public function test_salary_report_with_financial_year_filter()
    {
        // Create multiple employees
        $employee1 = Employee::factory()->create(['salary' => 60000, 'hire_date' => now()->subYear()]);
        $employee2 = Employee::factory()->create(['salary' => 50000, 'hire_date' => now()->subYear()]);

        // Create an account for salary payments
        $account = Account::factory()->create(['type' => 'asset']);

        // Create transactions for FY2024
        Transaction::factory()->create([
            'account_id' => $account->id,
            'employee_id' => $employee1->id,
            'transaction_type' => 'credit',
            'financial_year' => 'FY2024',
            'amount' => 50000,
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'employee_id' => $employee2->id,
            'transaction_type' => 'credit',
            'financial_year' => 'FY2024',
            'amount' => 40000,
        ]);

        // Create transaction for FY2023
        Transaction::factory()->create([
            'account_id' => $account->id,
            'employee_id' => $employee1->id,
            'transaction_type' => 'credit',
            'financial_year' => 'FY2023',
            'amount' => 45000,
        ]);

        // Test salary report filtered by FY2024
        $response = $this->getJson('/api/employees/reports/salary?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(2, $data['items']);
        
        // Verify only FY2024 data is included
        foreach ($data['items'] as $item) {
            $this->assertGreaterThan(0, $item['total_paid']);
            $this->assertLessThanOrEqual(60000, $item['total_paid']);
        }
    }

    public function test_employee_deductions_history()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Create various deductions
        Deduction::factory()->count(2)->create([
            'employee_id' => $employee->id,
            'financial_year' => 'FY2024',
            'deduction_type' => 'tax',
        ]);

        Deduction::factory()->create([
            'employee_id' => $employee->id,
            'financial_year' => 'FY2024',
            'deduction_type' => 'insurance',
        ]);

        // Create deductions for different FY
        Deduction::factory()->create([
            'employee_id' => $employee->id,
            'financial_year' => 'FY2023',
            'deduction_type' => 'tax',
        ]);

        // Test deductions report
        $response = $this->getJson('/api/deductions/reports/employee-deductions?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(1, $data['items']);
        
        $deductionReport = $data['items'][0];
        $this->assertEquals($employee->id, $deductionReport['employee']['id']);
        $this->assertArrayHasKey('total_deductions', $deductionReport);
        $this->assertArrayHasKey('deductions_by_type', $deductionReport);
        $this->assertArrayHasKey('tax', $deductionReport['deductions_by_type']);
        $this->assertArrayHasKey('insurance', $deductionReport['deductions_by_type']);
    }

    public function test_comprehensive_employee_salary_calculation()
    {
        // Create an employee with 6 months of work
        $employee = Employee::factory()->create([
            'salary' => 60000, // 5000 per month
            'hire_date' => now()->subMonths(6),
        ]);

        // Create an account for salary payments
        $account = Account::factory()->create(['type' => 'asset']);

        // Create salary payments for only 4 months
        $salaryPayments = Transaction::factory()->count(4)->create([
            'account_id' => $account->id,
            'employee_id' => $employee->id,
            'transaction_type' => 'credit',
            'amount' => 5000, // Full salary each month
            'financial_year' => 'FY2024',
        ]);

        // Create deductions
        $deduction1 = Deduction::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 500,
            'financial_year' => 'FY2024',
        ]);

        $deduction2 = Deduction::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 200,
            'financial_year' => 'FY2024',
        ]);

        $totalPaid = 4 * 5000; // 20000
        $totalDeductions = $deduction1->amount + $deduction2->amount; // 700
        $expectedSalary = 6 * 5000; // 30000 (6 months expected)
        $outstanding = $expectedSalary - $totalPaid; // 10000

        // Test comprehensive salary report
        $response = $this->getJson('/api/reports/employees/salary?financial_year=FY2024');

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(1, $data['items']);
        
        $salaryReport = $data['items'][0];
        $this->assertEquals($totalPaid, $salaryReport['total_paid']);
        $this->assertEquals($outstanding, $salaryReport['outstanding']);
        $this->assertGreaterThan(0, $salaryReport['salary_progress']); // Should be > 0 since they got paid
        $this->assertLessThan(100, $salaryReport['salary_progress']); // Should be < 100% since outstanding remains
    }
}