<?php

namespace App\Http\Controllers;

use App\Models\ModuleGroup;
use Illuminate\Http\Request;
use Exception;

class ModuleGroupController extends Controller
{
    public function index()
    {
        try {
            return response()->json(ModuleGroup::all());
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:modules_groups,name',
                'status' => 'required|string|in:active,inactive'
            ]);

            $group = ModuleGroup::create($request->all());

            return response()->json([
                'message' => 'Module Group created successfully',
                'group' => $group
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, ModuleGroup $moduleGroup)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:modules_groups,name,' . $moduleGroup->id,
                'status' => 'required|string|in:active,inactive'
            ]);

            $moduleGroup->update($request->all());

            return response()->json([
                'message' => 'Module Group updated successfully',
                'group' => $moduleGroup
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(ModuleGroup $moduleGroup)
    {
        try {
            if ($moduleGroup->modules()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete group with associated modules'
                ], 422);
            }

            $moduleGroup->delete();
            return response()->json(['message' => 'Module Group deleted successfully']);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
