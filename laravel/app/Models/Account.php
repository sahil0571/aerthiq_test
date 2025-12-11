<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'opening_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getBalanceAttribute()
    {
        $transactions = $this->transactions;
        
        $balance = $this->opening_balance ?? 0;
        
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'debit') {
                $balance -= $transaction->amount;
            } else {
                $balance += $transaction->amount;
            }
        }
        
        return $balance;
    }

    public function getAccountTypeOptions(): array
    {
        return [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense',
        ];
    }
}
