<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DeductionsController;
use App\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Account Routes
Route::prefix('accounts')->group(function () {
    Route::get('/', [AccountsController::class, 'index']);
    Route::post('/', [AccountsController::class, 'store']);
    Route::get('{id}', [AccountsController::class, 'show']);
    Route::put('{id}', [AccountsController::class, 'update']);
    Route::delete('{id}', [AccountsController::class, 'destroy']);
});

// Transaction Routes
Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionsController::class, 'index']);
    Route::post('/', [TransactionsController::class, 'store']);
    Route::get('{id}', [TransactionsController::class, 'show']);
    Route::put('{id}', [TransactionsController::class, 'update']);
    Route::delete('{id}', [TransactionsController::class, 'destroy']);
});

// Project Routes
Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectsController::class, 'index']);
    Route::post('/', [ProjectsController::class, 'store']);
    Route::get('{id}', [ProjectsController::class, 'show']);
    Route::put('{id}', [ProjectsController::class, 'update']);
    Route::delete('{id}', [ProjectsController::class, 'destroy']);
    Route::get('{id}/summary', [ProjectsController::class, 'summary']);
});

// Employee Routes
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeesController::class, 'index']);
    Route::post('/', [EmployeesController::class, 'store']);
    Route::get('{id}', [EmployeesController::class, 'show']);
    Route::put('{id}', [EmployeesController::class, 'update']);
    Route::delete('{id}', [EmployeesController::class, 'destroy']);
    Route::get('{id}/salary-history', [EmployeesController::class, 'salaryHistory']);
    Route::get('reports/salary', [EmployeesController::class, 'salaryReport']);
});

// Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoriesController::class, 'index']);
    Route::post('/', [CategoriesController::class, 'store']);
    Route::get('{id}', [CategoriesController::class, 'show']);
    Route::put('{id}', [CategoriesController::class, 'update']);
    Route::delete('{id}', [CategoriesController::class, 'destroy']);
});

// Deduction Routes
Route::prefix('deductions')->group(function () {
    Route::get('/', [DeductionsController::class, 'index']);
    Route::post('/', [DeductionsController::class, 'store']);
    Route::get('{id}', [DeductionsController::class, 'show']);
    Route::put('{id}', [DeductionsController::class, 'update']);
    Route::delete('{id}', [DeductionsController::class, 'destroy']);
    Route::get('reports/employee-deductions', [DeductionsController::class, 'employeeDeductionsReport']);
});

// Dashboard Routes
Route::prefix('dashboard')->group(function () {
    Route::get('summary', [ReportsController::class, 'dashboard']);
});

// Reports Routes
Route::prefix('reports')->group(function () {
    Route::get('financial-year/{financialYear}', [ReportsController::class, 'financialYearReport']);
    Route::get('projects', [ReportsController::class, 'projectReport']);
    Route::get('employees/salary', [ReportsController::class, 'employeeSalaryReport']);
    Route::get('profit-loss', [ReportsController::class, 'profitLossReport']);
    Route::get('balance-sheet', [ReportsController::class, 'balanceSheetReport']);
    Route::get('projects/{projectId}/financial-summary', [ReportsController::class, 'projectFinancialSummary']);
});

// Default API route for testing
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});