<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_assets' => (float) $this['total_assets'],
            'total_liabilities' => (float) $this['total_liabilities'],
            'total_equity' => (float) $this['total_equity'],
            'total_income' => (float) $this['total_income'],
            'total_expenses' => (float) $this['total_expenses'],
            'net_income' => (float) $this['net_income'],
            'active_projects' => (int) $this['active_projects'],
            'active_employees' => (int) $this['active_employees'],
            'recent_transactions' => TransactionResource::collection($this['recent_transactions']),
            'project_summaries' => ProjectSummaryResource::collection($this['project_summaries']),
        ];
    }
}