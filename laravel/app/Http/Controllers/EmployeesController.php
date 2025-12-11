<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'department', 'position', 'is_active', 'project_id', 'financial_year', 'search', 'size'
        ]);
        
        $employees = $this->employeeService->getFilteredEmployees($filters);
        
        return response()->json([
            'items' => EmployeeResource::collection($employees->items()),
            'total' => $employees->total(),
            'page' => $employees->currentPage(),
            'size' => $employees->perPage(),
            'pages' => $employees->lastPage(),
        ]);
    }

    public function show($id)
    {
        $employee = \App\Models\Employee::with(['transactions', 'project'])->findOrFail($id);
        
        return new EmployeeResource($employee);
    }

    public function store(EmployeeRequest $request)
    {
        $employee = $this->employeeService->createEmployee($request->validated());
        
        return new EmployeeResource($employee);
    }

    public function update(EmployeeRequest $request, $id)
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $updatedEmployee = $this->employeeService->updateEmployee($employee, $request->validated());
        
        return new EmployeeResource($updatedEmployee);
    }

    public function destroy($id)
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $employee->delete();
        
        return response()->json(['message' => 'Employee deleted successfully']);
    }

    public function salaryHistory($id)
    {
        $employee = \App\Models\Employee::with(['transactions', 'project'])->findOrFail($id);
        $salaryHistory = $this->employeeService->getEmployeeSalaryHistory($employee);
        
        return response()->json([
            'employee' => new EmployeeResource($employee),
            'total_paid' => $salaryHistory['total_paid'],
            'outstanding' => $salaryHistory['outstanding'],
            'recent_payments' => $salaryHistory['recent_payments'],
        ]);
    }

    public function salaryReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date'
        ]);
        
        $report = $this->employeeService->getSalaryReport($filters);
        
        return response()->json([
            'items' => $report,
            'total' => count($report),
        ]);
    }
}