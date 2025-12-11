<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'type', 'is_active', 'search', 'size'
        ]);
        
        $accounts = $this->accountService->getFilteredAccounts($filters);
        
        return response()->json([
            'items' => AccountResource::collection($accounts->items()),
            'total' => $accounts->total(),
            'page' => $accounts->currentPage(),
            'size' => $accounts->perPage(),
            'pages' => $accounts->lastPage(),
        ]);
    }

    public function show($id)
    {
        $account = \App\Models\Account::with('transactions')->findOrFail($id);
        
        return new AccountResource($account);
    }

    public function store(AccountRequest $request)
    {
        $account = $this->accountService->createAccount($request->validated());
        
        return new AccountResource($account);
    }

    public function update(AccountRequest $request, $id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $updatedAccount = $this->accountService->updateAccount($account, $request->validated());
        
        return new AccountResource($updatedAccount);
    }

    public function destroy($id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $account->delete();
        
        return response()->json(['message' => 'Account deleted successfully']);
    }
}