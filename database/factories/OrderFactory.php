<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Cafe;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Casts\Json;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cafe_id' => Cafe::factory(),
            'branch_id' => Branch::factory(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'online']),
            'table_number' => $this->faker->numberBetween(1, 30),
            'total_price' => 0.00,
            'notes' => $this->faker->optional()->sentence(8),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            if ($order->branch && $order->branch->cafe_id !== $order->cafe_id) {
                $branch = Branch::factory()->create(['cafe_id' => $order->cafe_id]);
                $order->branch()->associate($branch)->save();
            }

            $products = Product::where('cafe_id', $order->cafe_id)
                ->inRandomOrder()
                ->take(rand(1, 3))
                ->get();

            if ($products->isEmpty()) {
                $category = Category::firstOrCreate(
                    ['cafe_id' => $order->cafe_id, 'slug' => 'default-' . uniqid()],
                    ['name' => 'Default', 'is_active' => true]
                );
                $product = Product::factory()->count(3)->create([
                    'cafe_id' => $order->cafe_id,
                    'category_id' => $category->id,
                ]);
            }



            $total_price = '0.00';

            foreach ($products as $product) {
                $qty = rand(1, 2);
                $unit = (string) $product->price;
                $line = bcmul($unit, (string)$qty, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $line,
                ]);

                $total_price = bcadd($total_price, $line, 2);
            }

            $order->update(['total_price' => $total_price]);
        });
    }


    public function paid()
    {
        return $this->afterCreating(function (Order $order) {
            $order->payments()->create([
                'amount' => $order->total_price,
                'method' => 'mock',
                'status' => 'succeeded',
                'transaction_id' => "mock_" . uniqid(),
                'payload' => ['factory' => true],
                'processed_at' => now(),
            ]);
            $order->refreshPaymentStatus();
        });
    }
}
