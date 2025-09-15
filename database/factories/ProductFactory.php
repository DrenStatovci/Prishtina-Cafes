<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cafe;
use App\Models\Category;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement(['Espresso', 'Cappuccino', 'Latte', 'Çaj Mali', 'Croissant', 'Muffin', 'Sandwich', 'Salad', 'Smoothie', 'Juice', 'Water']);
        return [
            'cafe_id'     => Cafe::factory(),
            'category_id' => Category::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'price'       => $this->faker->randomFloat(2, 1.0, 4.0),
            'is_active'   => true,
            'description' => $this->faker->optional()->sentence(6),
            'image_url'   => null,
        ];
    }
}
