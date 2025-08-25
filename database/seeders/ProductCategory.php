<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategory extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $category = ['fashion', 'electronics', 'home_appliances', 'books', 'toys', 'sports', 'health_beauty'];


        foreach ($category as $cate){
            \App\Models\ProductCategory::firstOrCreate(
                [
                    'name' => $cate,
                    'parent_id' => null, // Assuming these are top-level categories
                ],
            );
        }
    }
}
