<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function getFilteredProjects(array $filters = [])
    {
        $query = Project::query()->with(['transactions', 'employees']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }
        
        if (isset($filters['financial_year'])) {
            $query->whereHas('transactions', function ($q) use ($filters) {
                $q->where('financial_year', $filters['financial_year']);
            });
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('client_name', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->paginate($filters['size'] ?? 15);
    }

    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create($data);
            return $project->load(['transactions', 'employees']);
        });
    }

    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
            return $project->fresh(['transactions', 'employees']);
        });
    }

    public function getProjectSummary(Project $project): array
    {
        return [
            'project' => $project,
            'total_income' => $project->total_income,
            'total_expense' => $project->total_expense,
            'balance' => $project->balance,
            'budget_utilization' => $project->budget ? ($project->total_expense / $project->budget) * 100 : 0,
            'profit_margin' => $project->total_income > 0 ? 
                (($project->total_income - $project->total_expense) / $project->total_income) * 100 : 0,
        ];
    }
}