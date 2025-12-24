<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        $categoryId = $request->query('category_id');
        
        $search = $request->query('search');
        
        $templates = Design::where('is_template', true)
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('category', function ($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('parent', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            });
                      });
                });
            })
            ->with(['category.parent']) // Eager load category and its parent
            ->latest()
            ->paginate($limit);

        return response()->json($templates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'cost' => 'integer|min:0',
            'sides' => 'string',
            'margin' => 'nullable|string',
            'colors' => 'string',
            'canvas_data' => 'nullable|array', // Optional for now, maybe created empty?
        ]);

        // If no canvas data provided, create empty default
        $canvasData = $request->canvas_data ?? ['version' => '5.3.0', 'objects' => []];

        $template = Design::create([
            'user_id' => Auth::id(), // Admin user
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'canvas_data' => $canvasData,
            'is_template' => true,
            'status' => 'approved', // Templates created by admin are auto-approved
            'cost' => $validated['cost'] ?? 0,
            'sides' => $validated['sides'] ?? 'Single',
            'margin' => $validated['margin'],
            'colors' => $validated['colors'] ?? 'CMYK',
            'is_active' => true,
        ]);

        return response()->json($template, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $template = Design::where('is_template', true)->findOrFail($id);
        return response()->json($template);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $template = Design::where('is_template', true)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'string',
            'category_id' => 'exists:categories,id',
            'cost' => 'integer|min:0',
            'sides' => 'string',
            'margin' => 'nullable|string',
            'colors' => 'string',
            'canvas_data' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json($template);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $template = Design::where('is_template', true)->findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }
}
