<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Constants\ResponseCode;
use Exception;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::with('permissions')->get();
            return response()->json($roles);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
            ]);

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, Role $role)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name,' . $role->id,
            ]);

            $role->update(['name' => $request->name]);

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Role $role)
    {
        try {
            if ($role->name === 'admin') {
                return response()->json(['message' => 'Cannot delete admin role'], 403);
            }
            $role->delete();
            return response()->json(['message' => 'Role deleted successfully']);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function assignPermissions(Request $request, Role $role)
    {
        try {
            $request->validate([
                'permissions' => 'present|array',
                'permissions.*' => 'string|exists:permissions,name'
            ]);

            $role->syncPermissions($request->permissions);

            return response()->json([
                'message' => 'Permissions assigned successfully',
                'role' => $role->load('permissions')
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
