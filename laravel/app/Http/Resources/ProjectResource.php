<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget' => (float) $this->budget,
            'status' => $this->status,
            'client_name' => $this->client_name,
            'total_income' => (float) $this->total_income,
            'total_expense' => (float) $this->total_expense,
            'balance' => (float) $this->balance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}