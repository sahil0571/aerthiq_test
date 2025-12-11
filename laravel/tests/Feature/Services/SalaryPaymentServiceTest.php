<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use App\Services\SalaryPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalaryPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalaryPaymentService $service;
    private Account $account;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalaryPaymentService();
        
        $this->account = Account::factory()->create([
            'code' => 'SAL001',
            'name' => 'Salary Account',
            'type' => 'asset',
        ]);

        $this->employee = Employee::factory()->create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'salary' => 5000.00,
            'hire_date' => '2024-01-01',
        ]);
    }

    /** @test */
    public function it_can_record_salary_payment()
    {
        $paymentData = [
            'date' => '2024-01-31',
            'amount' => 5000.00,
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
            'reference' => 'SAL-JAN-2024',
            'notes' => 'Monthly salary',
        ];

        $transaction = $this->service->recordSalaryPayment($this->employee, $paymentData);

        $this->assertDatabaseHas('transactions', [
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'description' => 'Salary Payment - John Doe',
            'financial_year' => '2024-2025',
        ]);

        $this->assertEquals('Salary Payment - John Doe', $transaction->description);
        $this->assertEquals('credit', $transaction->transaction_type);
    }

    /** @test */
    public function it_can_record_salary_payment_with_deductions()
    {
        $deduction = Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'date' => '2024-01-31',
            'deduction_type' => 'tax',
        ]);

        $paymentData = [
            'date' => '2024-01-31',
            'amount' => 5000.00,
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
            'deductions' => [
                ['deduction_id' => $deduction->id, 'amount_applied' => 500.00]
            ]
        ];

        $transaction = $this->service->recordSalaryPayment($this->employee, $paymentData);

        $this->assertDatabaseHas('transaction_deductions', [
            'transaction_id' => $transaction->id,
            'deduction_id' => $deduction->id,
            'amount_applied' => 500.00,
        ]);

        $this->assertTrue($transaction->deductions->contains($deduction));
    }

    /** @test */
    public function it_can_create_deductions_with_salary_payment()
    {
        $paymentData = [
            'date' => '2024-01-31',
            'amount' => 5000.00,
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
            'create_deductions' => [
                [
                    'amount' => 500.00,
                    'description' => 'Income Tax',
                    'date' => '2024-01-31',
                    'deduction_type' => 'tax',
                ]
            ]
        ];

        $transaction = $this->service->recordSalaryPayment($this->employee, $paymentData);

        $this->assertDatabaseHas('deductions', [
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'description' => 'Income Tax',
            'deduction_type' => 'tax',
            'financial_year' => '2024-2025',
        ]);

        $deduction = Deduction::where('employee_id', $this->employee->id)
                             ->where('description', 'Income Tax')
                             ->first();

        $this->assertNotNull($deduction);
        $this->assertTrue($transaction->deductions->contains($deduction));
    }

    /** @test */
    public function it_gets_salary_history_by_financial_year()
    {
        // Create transactions for the financial year
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-02-28',
            'financial_year' => '2024-2025',
        ]);

        // Create deduction for the FY
        Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        $history = $this->service->getSalaryHistoryByFinancialYear('2024-2025');

        $this->assertCount(1, $history);
        
        $employeeData = $history->first();
        $this->assertEquals(10000.00, $employeeData['total_paid']);
        $this->assertEquals(500.00, $employeeData['total_deductions']);
        $this->assertEquals(9500.00, $employeeData['net_salary']);
        $this->assertEquals(2, $employeeData['payment_count']);
        $this->assertArrayHasKey('monthly_breakdown', $employeeData);
    }

    /** @test */
    public function it_applies_filters_to_financial_year_salary_history()
    {
        $employee2 = Employee::factory()->create([
            'employee_code' => 'EMP002',
            'department' => 'IT',
            'salary' => 6000.00,
        ]);

        // Create salary payments
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $employee2->id,
            'amount' => 6000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        // Filter by department
        $filteredHistory = $this->service->getSalaryHistoryByFinancialYear('2024-2025', [
            'department' => 'IT'
        ]);

        $this->assertCount(1, $filteredHistory);
        $this->assertEquals(6000.00, $filteredHistory->first()['total_paid']);
    }

    /** @test */
    public function it_gets_salary_history_by_month()
    {
        // Create transaction in specific month
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
        ]);

        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-02-29', // Different month
        ]);

        $janHistory = $this->service->getSalaryHistoryByMonth(2024, 1);

        $this->assertCount(1, $janHistory);
        $this->assertEquals(5000.00, $janHistory->first()['total_paid']);
        $this->assertEquals(1, $janHistory->first()['payment_count']);
        $this->assertEquals('2024-01-31', $janHistory->first()['period']['end_date']);
    }

    /** @test */
    public function it_gets_monthly_salary_summary()
    {
        $employee2 = Employee::factory()->create([
            'employee_code' => 'EMP002',
        ]);

        // Create multiple payments in January
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
        ]);

        Transaction::factory()->create([
            'employee_id' => $employee2->id,
            'amount' => 6000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-15',
        ]);

        // Create deduction
        Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'date' => '2024-01-31',
        ]);

        $summary = $this->service->getMonthlySalarySummary(2024, 1);

        $this->assertEquals('2024-01-01', $summary['period']['start_date']);
        $this->assertEquals('2024-01-31', $summary['period']['end_date']);
        $this->assertEquals(11000.00, $summary['total_paid']);
        $this->assertEquals(500.00, $summary['total_deductions']);
        $this->assertEquals(10500.00, $summary['net_salary']);
        $this->assertEquals(2, $summary['payment_count']);
        $this->assertEquals(2, $summary['employee_count']);
        $this->assertEquals(5500.00, $summary['average_salary']);
    }

    /** @test */
    public function it_gets_financial_year_salary_summary()
    {
        $employee2 = Employee::factory()->create([
            'employee_code' => 'EMP002',
        ]);

        // Create multiple payments in the financial year
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-02-29',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $employee2->id,
            'amount' => 6000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-15',
            'financial_year' => '2024-2025',
        ]);

        // Create deductions
        Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        $summary = $this->service->getFinancialYearSalarySummary('2024-2025');

        $this->assertEquals('2024-2025', $summary['financial_year']);
        $this->assertEquals(16000.00, $summary['total_paid']);
        $this->assertEquals(1000.00, $summary['total_deductions']);
        $this->assertEquals(15000.00, $summary['net_salary']);
        $this->assertEquals(3, $summary['payment_count']);
        $this->assertEquals(2, $summary['employee_count']);
        $this->assertEquals(8000.00, $summary['average_salary']);
    }

    /** @test */
    public function it_calculates_monthly_breakdown_correctly()
    {
        // Create multiple transactions in different months
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-15', // Same month
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-02-28',
            'financial_year' => '2024-2025',
        ]);

        $history = $this->service->getSalaryHistoryByFinancialYear('2024-2025');
        $employeeData = $history->first();
        $monthlyBreakdown = $employeeData['monthly_breakdown'];

        $this->assertCount(2, $monthlyBreakdown);
        
        $janData = collect($monthlyBreakdown)->where('month', '2024-01')->first();
        $this->assertEquals(10000.00, $janData['total_amount']);
        $this->assertEquals(2, $janData['transaction_count']);

        $febData = collect($monthlyBreakdown)->where('month', '2024-02')->first();
        $this->assertEquals(5000.00, $febData['total_amount']);
        $this->assertEquals(1, $febData['transaction_count']);
    }

    /** @test */
    public function it_handles_employees_without_salary()
    {
        $employeeWithoutSalary = Employee::factory()->create([
            'employee_code' => 'EMP003',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'salary' => null,
        ]);

        Transaction::factory()->create([
            'employee_id' => $employeeWithoutSalary->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-31',
            'financial_year' => '2024-2025',
        ]);

        $history = $this->service->getSalaryHistoryByFinancialYear('2024-2025');

        $employeeData = $history->where('employee.id', $employeeWithoutSalary->id)->first();
        $this->assertEquals(5000.00, $employeeData['total_paid']);
        $this->assertEquals(0, $employeeData['outstanding']); // No outstanding without salary
    }
}