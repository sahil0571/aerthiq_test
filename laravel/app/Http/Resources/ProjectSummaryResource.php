<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'project' => new ProjectResource($this['project']),
            'total_income' => (float) $this['total_income'],
            'total_expense' => (float) $this['total_expense'],
            'balance' => (float) $this['balance'],
        ];
    }
}