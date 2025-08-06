<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fiction',
                'description' => 'Fictional stories and novels'
            ],
            [
                'name' => 'Non-Fiction',
                'description' => 'Factual books and real-life stories'
            ],
            [
                'name' => 'Science Fiction',
                'description' => 'Science fiction and fantasy novels'
            ],
            [
                'name' => 'Romance',
                'description' => 'Romance novels and love stories'
            ],
            [
                'name' => 'Mystery',
                'description' => 'Mystery and thriller books'
            ],
            [
                'name' => 'Biography',
                'description' => 'Biographies and autobiographies'
            ],
            [
                'name' => 'Self-Help',
                'description' => 'Self-improvement and personal development books'
            ],
            [
                'name' => 'History',
                'description' => 'Historical books and documentaries'
            ],
            [
                'name' => 'Technology',
                'description' => 'Technology and programming books'
            ],
            [
                'name' => 'Business',
                'description' => 'Business and entrepreneurship books'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
