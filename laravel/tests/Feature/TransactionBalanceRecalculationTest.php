<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TransactionBalanceRecalculationTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_creating_transaction_recalculates_account_balance()
    {
        // Create an account with opening balance
        $account = Account::factory()->create([
            'opening_balance' => 1000.00,
            'type' => 'asset'
        ]);

        $this->assertEquals(1000.00, $account->fresh()->balance);

        // Create a debit transaction
        $transactionData = [
            'date' => now()->toDateString(),
            'description' => 'Test debit transaction',
            'amount' => 200.00,
            'transaction_type' => 'debit',
            'account_id' => $account->id,
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201);
        
        // Check that account balance is recalculated (1000 - 200 = 800)
        $this->assertEquals(800.00, $account->fresh()->balance);
    }

    public function test_editing_transaction_recalculates_account_balance()
    {
        // Create an account with opening balance
        $account = Account::factory()->create([
            'opening_balance' => 1000.00,
            'type' => 'asset'
        ]);

        // Create a transaction
        $transaction = Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 200.00,
            'transaction_type' => 'debit',
        ]);

        $this->assertEquals(800.00, $account->fresh()->balance);

        // Update the transaction amount
        $updateData = [
            'date' => $transaction->date,
            'description' => 'Updated transaction',
            'amount' => 300.00,
            'transaction_type' => 'debit',
            'account_id' => $account->id,
        ];

        $response = $this->putJson("/api/transactions/{$transaction->id}", $updateData);

        $response->assertStatus(200);
        
        // Check that account balance is recalculated (1000 - 300 = 700)
        $this->assertEquals(700.00, $account->fresh()->balance);
    }

    public function test_deleting_transaction_recalculates_account_balance()
    {
        // Create an account with opening balance
        $account = Account::factory()->create([
            'opening_balance' => 1000.00,
            'type' => 'asset'
        ]);

        // Create a transaction
        $transaction = Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 200.00,
            'transaction_type' => 'debit',
        ]);

        $this->assertEquals(800.00, $account->fresh()->balance);

        // Delete the transaction
        $response = $this->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200);
        
        // Check that account balance is recalculated (back to opening balance)
        $this->assertEquals(1000.00, $account->fresh()->balance);
    }

    public function test_transaction_with_project_recalculates_project_totals()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create an account
        $account = Account::factory()->create(['type' => 'income']);

        // Create a credit transaction for the project
        $transactionData = [
            'date' => now()->toDateString(),
            'description' => 'Project income',
            'amount' => 1500.00,
            'transaction_type' => 'credit',
            'account_id' => $account->id,
            'project_id' => $project->id,
            'financial_year' => 'FY2024',
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201);
        
        // Check that project totals are calculated
        $this->assertEquals(1500.00, $project->fresh()->total_income);
        $this->assertEquals(1500.00, $project->fresh()->balance);
    }

    public function test_transaction_with_employee_updates_outstanding_salary()
    {
        // Create an employee with salary
        $employee = Employee::factory()->create([
            'salary' => 5000.00,
            'hire_date' => now()->subMonths(1),
        ]);

        // Create an account
        $account = Account::factory()->create(['type' => 'asset']);

        // Create a credit transaction for the employee (salary payment)
        $transactionData = [
            'date' => now()->toDateString(),
            'description' => 'Salary payment',
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'account_id' => $account->id,
            'employee_id' => $employee->id,
            'financial_year' => 'FY2024',
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201);
        
        // Check that employee total paid is updated
        $this->assertEquals(5000.00, $employee->fresh()->total_paid);
    }
}