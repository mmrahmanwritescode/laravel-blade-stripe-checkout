<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodItem = FoodItem::create(['name' => 'Eggs Benedict', 'price' => 10.99 , 'published' => 1]);
        $foodItem = FoodItem::create(['name' => 'Pancakes', 'price' => 7.99, 'published' => 1]);
        $foodItem = FoodItem::create(['name' => 'Chicken Sandwich', 'price' => 9.99, 'published' => 1]);
        $foodItem = FoodItem::create(['name' => 'Chicken Sandwich 3', 'price' => 9.99, 'published' => 1]);
        $foodItem = FoodItem::create(['name' => 'Chicken Sandwich 4', 'price' => 9.99, 'published' => 1]);
    }
}
