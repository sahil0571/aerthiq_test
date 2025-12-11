<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:20|unique:projects,code,' . $this->route('project'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|decimal:0,2|min:0',
            'status' => 'required|in:planned,active,completed,on_hold',
            'client_name' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Project code is required.',
            'code.unique' => 'Project code already exists.',
            'name.required' => 'Project name is required.',
            'status.required' => 'Project status is required.',
            'status.in' => 'Project status must be one of: planned, active, completed, on hold.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'budget.numeric' => 'Budget must be a number.',
            'budget.min' => 'Budget must be greater than or equal to 0.',
        ];
    }
}