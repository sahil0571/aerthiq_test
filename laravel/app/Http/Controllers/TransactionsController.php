<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'financial_year', 'start_date', 'end_date', 'project_id', 'employee_id', 
            'account_id', 'transaction_type', 'category', 'search', 'size'
        ]);
        
        $transactions = $this->transactionService->getFilteredTransactions($filters);
        
        return response()->json([
            'items' => TransactionResource::collection($transactions->items()),
            'total' => $transactions->total(),
            'page' => $transactions->currentPage(),
            'size' => $transactions->perPage(),
            'pages' => $transactions->lastPage(),
        ]);
    }

    public function show($id)
    {
        $transaction = \App\Models\Transaction::with(['account', 'project', 'employee'])->findOrFail($id);
        
        return new TransactionResource($transaction);
    }

    public function store(TransactionRequest $request)
    {
        $transaction = $this->transactionService->createTransaction($request->validated());
        
        return new TransactionResource($transaction);
    }

    public function update(TransactionRequest $request, $id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);
        $updatedTransaction = $this->transactionService->updateTransaction($transaction, $request->validated());
        
        return new TransactionResource($updatedTransaction);
    }

    public function destroy($id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);
        $this->transactionService->deleteTransaction($transaction);
        
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}