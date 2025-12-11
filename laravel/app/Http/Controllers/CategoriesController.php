<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $size = $request->get('size', 15);
        $categories = $query->paginate($size);
        
        return response()->json([
            'items' => CategoryResource::collection($categories->items()),
            'total' => $categories->total(),
            'page' => $categories->currentPage(),
            'size' => $categories->perPage(),
            'pages' => $categories->lastPage(),
        ]);
    }

    public function show($id)
    {
        $category = Category::with('transactions')->findOrFail($id);
        
        return new CategoryResource($category);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());
        
        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());
        
        return new CategoryResource($category->fresh());
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        
        return response()->json(['message' => 'Category deleted successfully']);
    }
}