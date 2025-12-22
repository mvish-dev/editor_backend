<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Constants\ResponseCode;
use Exception;

class PermissionController extends Controller
{
    public function index()
    {
        try {
            $permissions = Permission::all();
            return response()->json($permissions);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:permissions,name',
            ]);

            $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

            return response()->json([
                'message' => 'Permission created successfully',
                'permission' => $permission
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, Permission $permission)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:permissions,name,' . $permission->id,
            ]);

            $permission->update(['name' => $request->name]);

            return response()->json([
                'message' => 'Permission updated successfully',
                'permission' => $permission
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();
            return response()->json(['message' => 'Permission deleted successfully']);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
