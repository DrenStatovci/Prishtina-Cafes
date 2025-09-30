<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cafe;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class CafeGraphSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $owner = User::factory()->create(['email' => 'owner@demo.com']);
        $owner->assignRole('owner'); // Override default customer role

        $customer = User::factory()->create(['email' => 'customer@demo.com']);
        // Customer role is already assigned by factory

        $cafes = Cafe::factory()->count(2)->create(['owner_id' => $owner->id]);

        foreach ($cafes as $cafe) {
            $branches = Branch::factory()->count(2)->create(['cafe_id' => $cafe->id]);

            $categories = Category::factory()->count(3)->create(['cafe_id' => $cafe->id]);

            foreach ($categories as $category) {
                Product::factory()->count(4)->create([
                    'cafe_id' => $cafe->id,
                    'category_id' => $category->id,
                ]);
            }

            Order::factory()
                ->for($customer)
                ->for($cafe)
                ->for($branches->random())
                ->create();

            Order::factory()
                ->paid()
                ->for($customer)
                ->for($cafe)
                ->for($branches->random())
                ->create();
        }
    }
}
