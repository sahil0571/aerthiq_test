<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'transaction_type' => 'required|in:debit,credit',
            'account_id' => 'required|exists:accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'employee_id' => 'nullable|exists:employees,id',
            'category' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'financial_year' => 'nullable|string|max:10',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Transaction date is required.',
            'description.required' => 'Transaction description is required.',
            'amount.required' => 'Transaction amount is required.',
            'amount.min' => 'Transaction amount must be greater than 0.',
            'transaction_type.required' => 'Transaction type is required.',
            'transaction_type.in' => 'Transaction type must be either debit or credit.',
            'account_id.required' => 'Account selection is required.',
            'account_id.exists' => 'Selected account does not exist.',
            'project_id.exists' => 'Selected project does not exist.',
            'employee_id.exists' => 'Selected employee does not exist.',
        ];
    }
}