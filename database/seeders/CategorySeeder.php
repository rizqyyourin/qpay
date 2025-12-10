<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
            ],
            [
                'name' => 'Clothing',
                'description' => 'Clothes and apparel',
            ],
            [
                'name' => 'Books',
                'description' => 'Books and publications',
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Food items and drinks',
            ],
            [
                'name' => 'Furniture',
                'description' => 'Furniture and home decor',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
