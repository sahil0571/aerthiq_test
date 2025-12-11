<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department' => $this->department,
            'position' => $this->position,
            'hire_date' => $this->hire_date,
            'salary' => (float) $this->salary,
            'is_active' => $this->is_active,
            'total_paid' => (float) $this->total_paid,
            'outstanding' => (float) $this->outstanding,
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}