<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'parent'); // 'parent' or 'sub' or 'all'
        $perPage = $request->query('per_page', 10);

        $query = Category::query();

        if ($type === 'parent') {
            $query->whereNull('parent_id')->withCount('children');
        } elseif ($type === 'sub') {
            $query->whereNotNull('parent_id')->with('parent');
        } else {
            // 'all' or default fallback for other cases
            $query->with('children', 'parent');
        }

        // Add sorting
        $query->orderBy('created_at', 'desc');

        $categories = $query->paginate($perPage);
            
        return response()->json($categories);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('children', 'parent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
        ]);

        $category = Category::create($request->all());

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
        ]);

        $category->update($request->all());

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
