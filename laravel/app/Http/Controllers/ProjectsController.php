<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'start_date', 'end_date', 'financial_year', 'search', 'size'
        ]);
        
        $projects = $this->projectService->getFilteredProjects($filters);
        
        return response()->json([
            'items' => ProjectResource::collection($projects->items()),
            'total' => $projects->total(),
            'page' => $projects->currentPage(),
            'size' => $projects->perPage(),
            'pages' => $projects->lastPage(),
        ]);
    }

    public function show($id)
    {
        $project = \App\Models\Project::with(['transactions', 'employees'])->findOrFail($id);
        
        return new ProjectResource($project);
    }

    public function store(ProjectRequest $request)
    {
        $project = $this->projectService->createProject($request->validated());
        
        return new ProjectResource($project);
    }

    public function update(ProjectRequest $request, $id)
    {
        $project = \App\Models\Project::findOrFail($id);
        $updatedProject = $this->projectService->updateProject($project, $request->validated());
        
        return new ProjectResource($updatedProject);
    }

    public function destroy($id)
    {
        $project = \App\Models\Project::findOrFail($id);
        $project->delete();
        
        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function summary($id)
    {
        $project = \App\Models\Project::with(['transactions', 'employees'])->findOrFail($id);
        $summary = $this->projectService->getProjectSummary($project);
        
        return response()->json($summary);
    }
}