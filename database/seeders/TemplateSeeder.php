<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use App\Models\Design;
use App\Models\User;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/templates.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error('templates.json not found!');
            return;
        }

        $json = File::get($jsonPath);
        $templatesData = json_decode($json, true);

        // Get admin user to be the owner of templates
        $admin = User::where('email', 'admin@eprinton.com')->first();
        
        // Fallback if admin doesn't exist yet (though DatabaseSeeder should run first or concurrently)
        if (!$admin) {
            $admin = User::first() ?? User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@eprinton.com',
                'username' => 'admin',
                'password' => bcrypt('12345678'),
            ]);
        }

        foreach ($templatesData as $categoryName => $templates) {
            // 1. Create Main Category
            // Find default dimensions from the first template in this category, if any
            $defaultWidth = null;
            $defaultHeight = null;
            if (!empty($templates) && isset($templates[0]['width']) && isset($templates[0]['height'])) {
                $defaultWidth = $templates[0]['width'];
                $defaultHeight = $templates[0]['height'];
            }

            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'slug' => Str::slug($categoryName),
                    'width' => $defaultWidth,
                    'height' => $defaultHeight,
                ]
            );

            $this->command->info("Seeding Category: $categoryName");

            // 2. Create Sub-categories (Hospitals, Hotels)
            $subCategories = ['Hospitals', 'Hotels'];
            foreach ($subCategories as $subName) {
                Category::firstOrCreate(
                    [
                        'name' => $subName,
                        'parent_id' => $category->id
                    ],
                    [
                        'slug' => Str::slug($categoryName . '-' . $subName)
                    ]
                );
            }

            // 3. Create Templates
            foreach ($templates as $template) {
                // Check if design already exists to avoid duplicates
                $exists = Design::where('name', $template['name'])
                    ->where('category_id', $category->id)
                    ->exists();

                if (!$exists) {
                    Design::create([
                        'user_id' => $admin->id,
                        'category_id' => $category->id,
                        'name' => $template['name'],
                        'canvas_data' => json_encode($template), // Storing the whole template object structure as canvas_data? Or just 'objects'? 
                        // The frontend usually expects canvas JSON. The file has 'objects', 'width', 'height'.
                        // Fabric's loadFromJSON expects the whole object usually.
                        // I will store the whole $template array as canvas_data.
                        'image_url' => null, // We don't have generated images yet
                        'is_template' => true,
                        'status' => 'approved',
                    ]);
                }
            }
        }
    }
}
