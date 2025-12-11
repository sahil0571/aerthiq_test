<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'date',
        'description',
        'amount',
        'transaction_type',
        'account_id',
        'project_id',
        'employee_id',
        'category',
        'reference',
        'notes',
        'financial_year',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions()
    {
        return $this->belongsToMany(\App\Models\Deduction::class, 'transaction_deductions')
                    ->withPivot('amount_applied')
                    ->withTimestamps();
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'name');
    }

    public function getTransactionTypeOptions(): array
    {
        return [
            'debit' => 'Debit',
            'credit' => 'Credit',
        ];
    }
}
