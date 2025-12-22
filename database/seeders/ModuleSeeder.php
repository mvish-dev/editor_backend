<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            'Master' => ['Urgent Job', 'Types Of Work', 'Products']
        ];

        foreach ($groups as $groupName => $modules) {
            $group = \App\Models\ModuleGroup::create(['name' => $groupName]);
            foreach ($modules as $moduleName) {
                \App\Models\ModuleMaster::create([
                    'name' => $moduleName,
                    'group_id' => $group->id
                ]);

                // Create Spatie Permissions for each action
                $slug = Str::slug($moduleName, '_');
                $actions = ['view', 'create', 'update', 'delete', 'authorize'];
                foreach ($actions as $action) {
                    \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => $action . '_' . $slug,
                        'guard_name' => 'web'
                    ]);
                }
            }
        }
    }
}
