<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeductionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'deduction_type' => 'required|in:tax,insurance,loan,advance,other',
            'is_recurring' => 'boolean',
            'monthly_deduction' => 'nullable|numeric|decimal:0,2|min:0',
            'financial_year' => 'nullable|string|max:10',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee selection is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'amount.required' => 'Deduction amount is required.',
            'amount.min' => 'Deduction amount must be greater than 0.',
            'description.required' => 'Deduction description is required.',
            'date.required' => 'Deduction date is required.',
            'deduction_type.required' => 'Deduction type is required.',
            'deduction_type.in' => 'Deduction type must be one of: tax, insurance, loan, advance, other.',
        ];
    }
}