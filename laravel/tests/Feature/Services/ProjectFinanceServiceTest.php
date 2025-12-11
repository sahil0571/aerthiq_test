<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Employee;
use App\Models\Deduction;
use App\Services\ProjectFinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectFinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectFinanceService $service;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProjectFinanceService();
        
        $this->project = Project::factory()->create([
            'code' => 'PROJ001',
            'name' => 'Test Project',
            'status' => 'active',
            'budget' => 50000.00,
        ]);
    }

    /** @test */
    public function it_aggregates_project_finance_correctly()
    {
        // Create income transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 20000.00,
            'transaction_type' => 'credit',
            'category' => 'revenue',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 10000.00,
            'transaction_type' => 'credit',
            'category' => 'consulting',
            'financial_year' => '2024-2025',
        ]);

        // Create expense transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 5000.00,
            'transaction_type' => 'debit',
            'category' => 'equipment',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 3000.00,
            'transaction_type' => 'debit',
            'category' => 'travel',
            'financial_year' => '2024-2025',
        ]);

        $result = $this->service->aggregateProjectFinance(['financial_year' => '2024-2025']);

        $this->assertCount(1, $result);
        
        $projectData = $result->first();
        $this->assertEquals(30000.00, $projectData['total_income']);
        $this->assertEquals(8000.00, $projectData['total_expenses']);
        $this->assertEquals(22000.00, $projectData['net_profit']);
        $this->assertEquals(73.33, round($projectData['profit_margin'], 2));
        $this->assertEquals(42000.00, $projectData['budget_variance']);
        $this->assertEquals(16.00, round($projectData['budget_utilization'], 2));
    }

    /** @test */
    public function it_applies_filters_to_project_aggregation()
    {
        // Create another project
        $project2 = Project::factory()->create([
            'code' => 'PROJ002',
            'name' => 'Another Project',
            'status' => 'active',
        ]);

        // Create transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 10000.00,
            'transaction_type' => 'credit',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $project2->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'financial_year' => '2023-2024', // Different FY
        ]);

        $result = $this->service->aggregateProjectFinance(['financial_year' => '2024-2025']);

        $this->assertCount(1, $result);
        $this->assertEquals($this->project->id, $result->first()['project']->id);
    }

    /** @test */
    public function it_gets_outstanding_credit_card_balances()
    {
        // Create credit card transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 2000.00,
            'transaction_type' => 'debit',
            'category' => 'credit_card_expense',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 1500.00,
            'transaction_type' => 'debit',
            'category' => 'visa_charges',
            'financial_year' => '2024-2025',
        ]);

        $result = $this->service->getOutstandingCreditCardBalances(['financial_year' => '2024-2025']);

        $this->assertCount(1, $result);
        
        $projectData = $result->first();
        $this->assertEquals(3500.00, $projectData['total_credit_card_expenses']);
        $this->assertEquals(25000.00, $projectData['credit_limit']); // 50% of budget
        $this->assertEquals(0, $projectData['payments_made']); // No payments made
        $this->assertEquals(3500.00, $projectData['outstanding_balance']);
        $this->assertEquals(21500.00, $projectData['credit_available']);
        $this->assertEquals(2, $projectData['transaction_count']);
    }

    /** @test */
    public function it_calculates_credit_available_correctly()
    {
        // Create high credit card expenses that exceed typical limit
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 30000.00,
            'transaction_type' => 'debit',
            'category' => 'credit_card_expense',
            'financial_year' => '2024-2025',
        ]);

        $result = $this->service->getOutstandingCreditCardBalances(['financial_year' => '2024-2025']);

        $projectData = $result->first();
        $this->assertEquals(30000.00, $projectData['total_credit_card_expenses']);
        $this->assertEquals(0, $projectData['credit_available']); // Over limit
        $this->assertEquals(0, $projectData['outstanding_balance']); // Still shows 0 since no payments
    }

    /** @test */
    public function it_gets_deductions_by_project()
    {
        $employee1 = Employee::factory()->create([
            'project_id' => $this->project->id,
            'salary' => 5000.00,
        ]);

        $employee2 = Employee::factory()->create([
            'project_id' => $this->project->id,
            'salary' => 6000.00,
        ]);

        // Create deductions for employees
        Deduction::factory()->create([
            'employee_id' => $employee1->id,
            'amount' => 500.00,
            'deduction_type' => 'tax',
            'financial_year' => '2024-2025',
        ]);

        Deduction::factory()->create([
            'employee_id' => $employee1->id,
            'amount' => 200.00,
            'deduction_type' => 'insurance',
            'financial_year' => '2024-2025',
        ]);

        Deduction::factory()->create([
            'employee_id' => $employee2->id,
            'amount' => 600.00,
            'deduction_type' => 'tax',
            'financial_year' => '2024-2025',
        ]);

        $result = $this->service->getDeductionsByProject(['financial_year' => '2024-2025']);

        $this->assertCount(1, $result);
        
        $projectData = $result->first();
        $this->assertEquals(1300.00, $projectData['total_deductions']);
        $this->assertEquals(3, $projectData['deduction_count']);
        $this->assertEquals(2, $projectData['employee_count']);
        $this->assertEquals(0, $projectData['recurring_deductions']); // None marked as recurring
    }

    /** @test */
    public function it_filters_deductions_by_type()
    {
        $employee = Employee::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Create different types of deductions
        Deduction::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 500.00,
            'deduction_type' => 'tax',
            'financial_year' => '2024-2025',
        ]);

        Deduction::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 200.00,
            'deduction_type' => 'insurance',
            'financial_year' => '2024-2025',
        ]);

        $result = $this->service->getDeductionsByProject([
            'financial_year' => '2024-2025',
            'deduction_type' => 'tax'
        ]);

        $projectData = $result->first();
        $this->assertEquals(500.00, $projectData['total_deductions']);
        $this->assertEquals(1, $projectData['deduction_count']);
    }

    /** @test */
    public function it_gets_comprehensive_project_finance_report()
    {
        $employee = Employee::factory()->create([
            'project_id' => $this->project->id,
            'salary' => 5000.00,
        ]);

        // Create transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 20000.00,
            'transaction_type' => 'credit',
            'category' => 'revenue',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 5000.00,
            'transaction_type' => 'debit',
            'category' => 'expenses',
            'financial_year' => '2024-2025',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 2000.00,
            'transaction_type' => 'debit',
            'category' => 'credit_card_expense',
            'financial_year' => '2024-2025',
        ]);

        // Create deductions
        Deduction::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 500.00,
            'deduction_type' => 'tax',
            'financial_year' => '2024-2025',
        ]);

        $report = $this->service->getComprehensiveProjectFinanceReport(['financial_year' => '2024-2025']);

        // Check summary
        $this->assertEquals(20000.00, $report['summary']['total_income']);
        $this->assertEquals(5000.00, $report['summary']['total_expenses']);
        $this->assertEquals(15000.00, $report['summary']['total_net_profit']);
        $this->assertEquals(2000.00, $report['summary']['total_credit_card_outstanding']);
        $this->assertEquals(500.00, $report['summary']['total_project_deductions']);
        $this->assertEquals(1, $report['summary']['project_count']);

        // Check project data
        $this->assertCount(1, $report['projects']);
        $projectData = $report['projects']->first();
        
        $this->assertArrayHasKey('credit_card_info', $projectData);
        $this->assertArrayHasKey('deduction_info', $projectData);
        
        $this->assertEquals(2000.00, $projectData['credit_card_info']['outstanding_balance']);
        $this->assertEquals(500.00, $projectData['deduction_info']['total_deductions']);
    }

    /** @test */
    public function it_calculates_project_financial_metrics()
    {
        $employee1 = Employee::factory()->create([
            'project_id' => $this->project->id,
            'salary' => 5000.00,
        ]);

        $employee2 = Employee::factory()->create([
            'project_id' => $this->project->id,
            'salary' => 6000.00,
        ]);

        // Create transactions
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 30000.00,
            'transaction_type' => 'credit',
            'category' => 'revenue',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 10000.00,
            'transaction_type' => 'debit',
            'category' => 'expenses',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 2000.00,
            'transaction_type' => 'debit',
            'category' => 'visa_charges',
        ]);

        $metrics = $this->service->calculateProjectFinancialMetrics($this->project);

        $this->assertEquals(30000.00, $metrics['total_income']);
        $this->assertEquals(12000.00, $metrics['total_expenses']);
        $this->assertEquals(18000.00, $metrics['net_profit']);
        $this->assertEquals(60.00, round($metrics['profit_margin'], 2));
        $this->assertEquals(132000.00, $metrics['employee_costs']); // (5000 + 6000) * 12
        $this->assertEquals(2000.00, $metrics['credit_card_expenses']);
        
        // Budget utilization
        $this->assertEquals(24.00, round($metrics['budget_utilization'], 2)); // 12000 / 50000 * 100
    }

    /** @test */
    public function it_handles_empty_projects_gracefully()
    {
        // Create project with no transactions
        $emptyProject = Project::factory()->create([
            'code' => 'EMPTY001',
            'name' => 'Empty Project',
            'budget' => 10000.00,
        ]);

        $result = $this->service->aggregateProjectFinance();

        $emptyProjectData = $result->where('project.id', $emptyProject->id)->first();
        $this->assertEquals(0.00, $emptyProjectData['total_income']);
        $this->assertEquals(0.00, $emptyProjectData['total_expenses']);
        $this->assertEquals(0.00, $emptyProjectData['net_profit']);
        $this->assertEquals(0, $emptyProjectData['transaction_count']);
    }

    /** @test */
    public function it_respects_date_filters_for_project_aggregation()
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        // Create transactions in different months
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'date' => '2024-01-15', // Within range
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 3000.00,
            'transaction_type' => 'credit',
            'date' => '2024-02-15', // Outside range
        ]);

        $result = $this->service->aggregateProjectFinance([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $projectData = $result->first();
        $this->assertEquals(5000.00, $projectData['total_income']);
        $this->assertEquals(0.00, $projectData['total_expenses']);
    }

    /** @test */
    public function it_calculates_category_breakdown_correctly()
    {
        // Create transactions with different categories
        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 10000.00,
            'transaction_type' => 'credit',
            'category' => 'revenue',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
            'category' => 'consulting',
        ]);

        Transaction::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 3000.00,
            'transaction_type' => 'debit',
            'category' => 'equipment',
        ]);

        $result = $this->service->aggregateProjectFinance();
        $projectData = $result->first();
        $breakdown = $projectData['category_breakdown'];

        $this->assertArrayHasKey('revenue', $breakdown);
        $this->assertArrayHasKey('consulting', $breakdown);
        $this->assertArrayHasKey('equipment', $breakdown);

        $this->assertEquals(10000.00, $breakdown['revenue']['income']);
        $this->assertEquals(0, $breakdown['revenue']['expenses']);
        $this->assertEquals(10000.00, $breakdown['revenue']['net']);
        $this->assertEquals(1, $breakdown['revenue']['transaction_count']);

        $this->assertEquals(0, $breakdown['equipment']['income']);
        $this->assertEquals(3000.00, $breakdown['equipment']['expenses']);
        $this->assertEquals(-3000.00, $breakdown['equipment']['net']);
        $this->assertEquals(1, $breakdown['equipment']['transaction_count']);
    }

    /** @test */
    public function it_applies_status_filter_to_projects()
    {
        $activeProject = $this->project;
        $completedProject = Project::factory()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed Project',
            'status' => 'completed',
        ]);

        // Create transactions for both projects
        Transaction::factory()->create([
            'project_id' => $activeProject->id,
            'amount' => 5000.00,
            'transaction_type' => 'credit',
        ]);

        Transaction::factory()->create([
            'project_id' => $completedProject->id,
            'amount' => 3000.00,
            'transaction_type' => 'credit',
        ]);

        $result = $this->service->aggregateProjectFinance(['status' => 'active']);

        $this->assertCount(1, $result);
        $this->assertEquals($activeProject->id, $result->first()['project']->id);
    }
}