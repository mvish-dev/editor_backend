<?php

namespace App\Http\Controllers;

use App\Models\ModuleMaster;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

class ModuleMasterController extends Controller
{
    public function index()
    {
        try {
            return response()->json(ModuleMaster::with('group')->get());
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:modules_masters,name',
                'group_id' => 'required|exists:modules_groups,id',
                'status' => 'required|string|in:active,inactive'
            ]);

            $module = ModuleMaster::create($request->all());

            // Auto-sync Spatie permissions
            $this->syncPermissions($module);

            return response()->json([
                'message' => 'Module created successfully',
                'module' => $module
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, ModuleMaster $module)
    {
        try {
            $oldName = $module->name;
            $request->validate([
                'name' => 'required|string|unique:modules_masters,name,' . $module->id,
                'group_id' => 'required|exists:modules_groups,id',
                'status' => 'required|string|in:active,inactive'
            ]);

            $module->update($request->all());

            // If name changed, update permission names
            if ($oldName !== $module->name) {
                $this->syncPermissions($module, $oldName);
            }

            return response()->json([
                'message' => 'Module updated successfully',
                'module' => $module
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(ModuleMaster $module)
    {
        try {
            // Delete associated permissions if necessary, but keep for safety/role stability usually.
            // For now, we delete to keep permissions table clean with module management.
            $slug = Str::slug($module->name, '_');
            Permission::where('name', 'like', "%_{$slug}")->delete();

            $module->delete();
            return response()->json(['message' => 'Module deleted successfully']);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function syncPermissions(ModuleMaster $module, $oldName = null)
    {
        $actions = ['view', 'create', 'update', 'delete', 'authorize'];
        $newSlug = Str::slug($module->name, '_');
        
        if ($oldName) {
            $oldSlug = Str::slug($oldName, '_');
            foreach ($actions as $action) {
                $oldPermName = $action . '_' . $oldSlug;
                $newPermName = $action . '_' . $newSlug;
                
                $permission = Permission::where('name', $oldPermName)->first();
                if ($permission) {
                    $permission->update(['name' => $newPermName]);
                } else {
                    Permission::firstOrCreate(['name' => $newPermName, 'guard_name' => 'web']);
                }
            }
        } else {
            foreach ($actions as $action) {
                $permName = $action . '_' . $newSlug;
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }
    }
}
