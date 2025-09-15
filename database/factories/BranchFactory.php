<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cafe;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->city() . ' Center';
        return [
            'cafe_id' => Cafe::Factory(),
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'address' => $this->faker->address(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'is_active' => true,
            'opening_hours' => [
                'mon-fri' => '8:00-23:00',
                'sat' => '9:00-00:00',
                'sun' => '10:00-20:00',
            ]
        ];
    }
}
