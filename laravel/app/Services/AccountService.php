<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;

class AccountService
{
    public function calculateAccountBalance(Account $account): float
    {
        $transactions = $account->transactions;
        $balance = $account->opening_balance ?? 0;
        
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'debit') {
                $balance -= $transaction->amount;
            } else {
                $balance += $transaction->amount;
            }
        }
        
        return $balance;
    }

    public function getFilteredAccounts(array $filters = [])
    {
        $query = Account::query()->with('transactions');
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->paginate($filters['size'] ?? 15);
    }

    public function createAccount(array $data): Account
    {
        $account = Account::create($data);
        
        // Create opening balance transaction if provided
        if (isset($data['opening_balance']) && $data['opening_balance'] != 0) {
            Transaction::create([
                'account_id' => $account->id,
                'date' => now(),
                'description' => 'Opening Balance',
                'amount' => abs($data['opening_balance']),
                'transaction_type' => $data['opening_balance'] > 0 ? 'credit' : 'debit',
                'reference' => 'OPENING',
            ]);
        }
        
        return $account->fresh(['transactions']);
    }

    public function updateAccount(Account $account, array $data): Account
    {
        $account->update($data);
        return $account->fresh(['transactions']);
    }
}