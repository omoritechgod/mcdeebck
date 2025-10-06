<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Phones & Tablets',
            'Electronics',
            'Vehicles',
            'Property',
            'Home, Furniture & Appliances',
            'Health & Beauty',
            'Fashion',
            'Sports, Arts & Outdoors',
            'Jobs',
            'Services',
            'Pets',
            'Babies & Kids',
            'Agriculture & Food',
            'Commercial Equipment & Tools'
        ];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
