<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeductionRequest;
use App\Http\Resources\DeductionResource;
use App\Services\DeductionService;
use Illuminate\Http\Request;

class DeductionsController extends Controller
{
    protected $deductionService;

    public function __construct(DeductionService $deductionService)
    {
        $this->deductionService = $deductionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'employee_id', 'deduction_type', 'is_recurring', 'start_date', 'end_date', 
            'financial_year', 'search', 'size'
        ]);
        
        $deductions = $this->deductionService->getFilteredDeductions($filters);
        
        return response()->json([
            'items' => DeductionResource::collection($deductions->items()),
            'total' => $deductions->total(),
            'page' => $deductions->currentPage(),
            'size' => $deductions->perPage(),
            'pages' => $deductions->lastPage(),
        ]);
    }

    public function show($id)
    {
        $deduction = \App\Models\Deduction::with('employee')->findOrFail($id);
        
        return new DeductionResource($deduction);
    }

    public function store(DeductionRequest $request)
    {
        $deduction = $this->deductionService->createDeduction($request->validated());
        
        return new DeductionResource($deduction);
    }

    public function update(DeductionRequest $request, $id)
    {
        $deduction = \App\Models\Deduction::findOrFail($id);
        $updatedDeduction = $this->deductionService->updateDeduction($deduction, $request->validated());
        
        return new DeductionResource($updatedDeduction);
    }

    public function destroy($id)
    {
        $deduction = \App\Models\Deduction::findOrFail($id);
        $deduction->delete();
        
        return response()->json(['message' => 'Deduction deleted successfully']);
    }

    public function employeeDeductionsReport(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date'
        ]);
        
        $report = $this->deductionService->getEmployeeDeductionsReport($filters);
        
        return response()->json([
            'items' => array_values($report),
            'total' => count($report),
        ]);
    }
}