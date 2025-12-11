<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'position',
        'hire_date',
        'salary',
        'is_active',
        'project_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(Deduction::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->transactions()
            ->where('transaction_type', 'credit')
            ->sum('amount');
    }

    public function getOutstandingAttribute()
    {
        $expectedSalary = $this->getExpectedSalary();
        return $expectedSalary - $this->total_paid;
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    private function getExpectedSalary(): float
    {
        if (!$this->hire_date || !$this->salary) {
            return 0;
        }

        $monthsWorked = $this->hire_date->diffInMonths(now()) + 1;
        return $this->salary * $monthsWorked;
    }
}
