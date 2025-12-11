<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'employee_id',
        'amount',
        'description',
        'date',
        'deduction_type',
        'is_recurring',
        'monthly_deduction',
        'financial_year',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'transaction_deductions')
                    ->withPivot('amount_applied')
                    ->withTimestamps();
    }

    public function getDeductionTypeOptions(): array
    {
        return [
            'tax' => 'Tax',
            'insurance' => 'Insurance',
            'loan' => 'Loan',
            'advance' => 'Advance',
            'other' => 'Other',
        ];
    }
}
