<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Deduction;
use App\Services\TransactionManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionManagementService $service;
    private Account $account;
    private Project $project;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionManagementService();
        
        $this->account = Account::factory()->create([
            'code' => 'ACC001',
            'name' => 'Cash Account',
            'type' => 'asset',
            'opening_balance' => 1000.00,
        ]);

        $this->project = Project::factory()->create([
            'code' => 'PROJ001',
            'name' => 'Test Project',
            'status' => 'active',
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
    public function it_can_create_transaction_with_deductions()
    {
        $transactionData = [
            'date' => '2024-01-15',
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'transaction_type' => 'debit',
            'account_id' => $this->account->id,
            'project_id' => $this->project->id,
            'employee_id' => $this->employee->id,
            'financial_year' => '2024-2025',
        ];

        $deduction1 = Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 50.00,
            'description' => 'Test Deduction 1',
            'date' => '2024-01-15',
            'deduction_type' => 'tax',
        ]);

        $deductionsData = [
            ['deduction_id' => $deduction1->id, 'amount_applied' => 25.00]
        ];

        $transaction = $this->service->createTransactionWithDeductions($transactionData, $deductionsData);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'transaction_type' => 'debit',
        ]);

        $this->assertDatabaseHas('transaction_deductions', [
            'transaction_id' => $transaction->id,
            'deduction_id' => $deduction1->id,
            'amount_applied' => 25.00,
        ]);

        $this->assertTrue($transaction->deductions->contains($deduction1));
    }

    /** @test */
    public function it_can_update_transaction_with_deductions()
    {
        $transaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'amount' => 100.00,
            'transaction_type' => 'credit',
            'financial_year' => '2024-2025',
        ]);

        $deduction = Deduction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 30.00,
            'date' => '2024-01-15',
        ]);

        $updateData = ['amount' => 150.00];
        $deductionsData = [['deduction_id' => $deduction->id, 'amount_applied' => 30.00]];

        $updatedTransaction = $this->service->updateTransactionWithDeductions($transaction, $updateData, $deductionsData);

        $this->assertEquals(150.00, $updatedTransaction->fresh()->amount);
        $this->assertTrue($updatedTransaction->deductions->contains($deduction));
    }

    /** @test */
    public function it_can_delete_transaction()
    {
        $transaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'project_id' => $this->project->id,
            'employee_id' => $this->employee->id,
        ]);

        $deduction = Deduction::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        // Attach deduction to transaction
        $transaction->deductions()->attach($deduction->id, ['amount_applied' => 25.00]);

        $result = $this->service->deleteTransaction($transaction);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseMissing('transaction_deductions', ['transaction_id' => $transaction->id]);
    }

    /** @test */
    public function it_calculates_computed_account_balance_correctly()
    {
        // Create initial balance
        $this->assertEquals(1000.00, $this->service->getComputedAccountBalance($this->account));

        // Add credit transaction
        $creditTransaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'amount' => 500.00,
            'transaction_type' => 'credit',
        ]);

        // Balance should be: 1000 + 500 = 1500
        $this->assertEquals(1500.00, $this->service->getComputedAccountBalance($this->account->fresh()));

        // Add debit transaction
        $debitTransaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'amount' => 200.00,
            'transaction_type' => 'debit',
        ]);

        // Balance should be: 1500 - 200 = 1300
        $this->assertEquals(1300.00, $this->service->getComputedAccountBalance($this->account->fresh()));
    }

    /** @test */
    public function it_calculates_computed_project_balance_correctly()
    {
        // Add income transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 2000.00,
            'transaction_type' => 'credit',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 1000.00,
            'transaction_type' => 'credit',
        ]);

        // Add expense transaction
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 800.00,
            'transaction_type' => 'debit',
        ]);

        // Project balance should be: 2000 + 1000 - 800 = 2200
        $this->assertEquals(2200.00, $this->service->getComputedProjectBalance($this->project->fresh()));
    }

    /** @test */
    public function it_calculates_computed_employee_outstanding_correctly()
    {
        // Employee salary is 5000, hired 2024-01-01
        // By now (assuming current date), should have expected salary for months worked

        $monthsWorked = $this->employee->hire_date->diffInMonths(now()) + 1;
        $expectedSalary = $this->employee->salary * $monthsWorked;

        // Add some salary payments
        Transaction::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
        ]);

        $expectedOutstanding = $expectedSalary - 5000;
        $actualOutstanding = $this->service->getComputedEmployeeOutstanding($this->employee->fresh());

        $this->assertEquals(round($expectedOutstanding, 2), round($actualOutstanding, 2));
    }

    /** @test */
    public function it_filters_transactions_by_financial_year()
    {
        // Create transactions for different financial years
        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'financial_year' => '2023-2024',
        ]);

        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
        ]);

        $fy2024Transactions = $this->service->getTransactionsByFinancialYear('2024-2025');

        $this->assertCount(2, $fy2024Transactions);
        $this->assertTrue($fy2024Transactions->every(fn($t) => $t->financial_year === '2024-2025'));
    }

    /** @test */
    public function it_applies_additional_filters_to_transactions()
    {
        // Create transactions with different types
        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'transaction_type' => 'credit',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'transaction_type' => 'debit',
            'financial_year' => '2024-2025',
        ]);

        $creditTransactions = $this->service->getTransactionsByFinancialYear('2024-2025', [
            'transaction_type' => 'credit'
        ]);

        $this->assertCount(1, $creditTransactions);
        $this->assertEquals('credit', $creditTransactions->first()->transaction_type);
    }

    /** @test */
    public function it_handles_empty_transaction_deductions()
    {
        $transactionData = [
            'date' => '2024-01-15',
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'transaction_type' => 'debit',
            'account_id' => $this->account->id,
            'financial_year' => '2024-2025',
        ];

        $transaction = $this->service->createTransactionWithDeductions($transactionData, []);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Test Transaction',
            'amount' => 100.00,
        ]);

        $this->assertEquals(0, $transaction->deductions->count());
    }
}