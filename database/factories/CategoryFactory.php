<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cafe;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Kafe', 'Caj', 'Embelsira', 'Pije te ftohta', 'Pije Alkoolike', 'Meze', 'Embelsira']);
        return [
            'cafe_id' => Cafe::factory(),
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'is_active' => true,
        ];
    }
}
