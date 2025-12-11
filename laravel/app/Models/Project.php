<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'status',
        'client_name',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function getTotalIncomeAttribute()
    {
        return $this->transactions()
            ->where('transaction_type', 'credit')
            ->sum('amount');
    }

    public function getTotalExpenseAttribute()
    {
        return $this->transactions()
            ->where('transaction_type', 'debit')
            ->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->total_income - $this->total_expense;
    }

    public function getStatusOptions(): array
    {
        return [
            'planned' => 'Planned',
            'active' => 'Active',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
        ];
    }
}
