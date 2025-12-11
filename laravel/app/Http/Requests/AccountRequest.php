<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:20|unique:accounts,code,' . $this->route('account'),
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'category' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric|decimal:0,2',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Account code is required.',
            'code.unique' => 'Account code already exists.',
            'name.required' => 'Account name is required.',
            'type.required' => 'Account type is required.',
            'type.in' => 'Account type must be one of: asset, liability, equity, income, expense.',
            'opening_balance.numeric' => 'Opening balance must be a number.',
            'opening_balance.decimal' => 'Opening balance must have at most 2 decimal places.',
        ];
    }
}