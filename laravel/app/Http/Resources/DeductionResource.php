<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeductionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'date' => $this->date,
            'deduction_type' => $this->deduction_type,
            'is_recurring' => $this->is_recurring,
            'monthly_deduction' => (float) $this->monthly_deduction,
            'financial_year' => $this->financial_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}