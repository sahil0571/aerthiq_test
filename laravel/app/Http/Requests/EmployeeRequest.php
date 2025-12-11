<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_code' => 'required|string|max:20|unique:employees,employee_code,' . $this->route('employee'),
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:employees,email,' . $this->route('employee'),
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|decimal:0,2|min:0',
            'is_active' => 'boolean',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }

    public function messages()
    {
        return [
            'employee_code.required' => 'Employee code is required.',
            'employee_code.unique' => 'Employee code already exists.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'Email address already exists.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary must be greater than or equal to 0.',
        ];
    }
}