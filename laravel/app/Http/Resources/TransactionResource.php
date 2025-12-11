<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'transaction_type' => $this->transaction_type,
            'account_id' => $this->account_id,
            'account' => new AccountResource($this->whenLoaded('account')),
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'category' => $this->category,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'financial_year' => $this->financial_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}