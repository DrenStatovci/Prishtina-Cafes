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
use App\Models\StaffProfile;

class ComprehensiveCafeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users with specific roles
        $admin = User::factory()->create([
            'email' => 'admin@demo.com',
            'name' => 'Admin User'
        ]);
        $admin->assignRole('admin');

        $owner1 = User::factory()->create([
            'email' => 'owner@demo.com',
            'name' => 'Cafe Owner'
        ]);
        $owner1->assignRole('owner');

        $owner2 = User::factory()->create([
            'email' => 'owner2@demo.com',
            'name' => 'Second Owner'
        ]);
        $owner2->assignRole('owner');

        // Create staff members
        $manager = User::factory()->create([
            'email' => 'manager@demo.com',
            'name' => 'Cafe Manager'
        ]);
        $manager->assignRole('manager');

        $waiter1 = User::factory()->create([
            'email' => 'waiter@demo.com',
            'name' => 'John Waiter'
        ]);
        $waiter1->assignRole('waiter');

        $waiter2 = User::factory()->create([
            'email' => 'waiter2@demo.com',
            'name' => 'Sarah Waiter'
        ]);
        $waiter2->assignRole('waiter');

        $bartender = User::factory()->create([
            'email' => 'bartender@demo.com',
            'name' => 'Mike Bartender'
        ]);
        $bartender->assignRole('bartender');

        // Create customers
        $customer1 = User::factory()->create([
            'email' => 'customer@demo.com',
            'name' => 'Regular Customer'
        ]);

        $customer2 = User::factory()->create([
            'email' => 'customer2@demo.com',
            'name' => 'VIP Customer'
        ]);

        $customer3 = User::factory()->create([
            'email' => 'customer3@demo.com',
            'name' => 'New Customer'
        ]);

        // Create more customers
        $customers = collect([$customer1, $customer2, $customer3]);
        for ($i = 4; $i <= 10; $i++) {
            $customers->push(User::factory()->create([
                'email' => "customer{$i}@demo.com",
                'name' => "Customer {$i}"
            ]));
        }

        // Create realistic cafes
        $cafe1 = Cafe::create([
            'owner_id' => $owner1->id,
            'name' => 'Prishtina Central Cafe',
            'slug' => 'prishtina-central-cafe',
            'email' => 'info@prishtinacentral.com',
            'phone' => '+383 44 123 456',
            'description' => 'The heart of Prishtina\'s coffee culture, serving premium coffee and fresh pastries in a modern setting.',
            'is_active' => true,
        ]);

        $cafe2 = Cafe::create([
            'owner_id' => $owner2->id,
            'name' => 'Green Bean Coffee House',
            'slug' => 'green-bean-coffee-house',
            'email' => 'hello@greenbean.com',
            'phone' => '+383 44 789 012',
            'description' => 'Sustainable coffee roasting and organic food in an eco-friendly environment.',
            'is_active' => true,
        ]);

        $cafe3 = Cafe::create([
            'owner_id' => $owner1->id,
            'name' => 'Brew & Bites Downtown',
            'slug' => 'brew-bites-downtown',
            'email' => 'contact@brewbites.com',
            'phone' => '+383 44 345 678',
            'description' => 'Artisanal coffee and gourmet sandwiches in the bustling downtown area.',
            'is_active' => true,
        ]);

        // Create branches for each cafe
        $cafe1Branches = [
            Branch::create([
                'cafe_id' => $cafe1->id,
                'name' => 'Main Street',
                'slug' => 'main-street',
                'address' => 'Rr. Nëna Terezë 123, Prishtinë',
                'phone' => '+383 44 123 457',
                'opening_hours' => json_encode(['monday' => '07:00-22:00', 'tuesday' => '07:00-22:00', 'wednesday' => '07:00-22:00', 'thursday' => '07:00-22:00', 'friday' => '07:00-22:00', 'saturday' => '07:00-22:00', 'sunday' => '07:00-22:00']),
                'is_active' => true,
            ]),
            Branch::create([
                'cafe_id' => $cafe1->id,
                'name' => 'University Campus',
                'slug' => 'university-campus',
                'address' => 'Rr. Agim Ramadani, Campus i UP-së',
                'phone' => '+383 44 123 458',
                'opening_hours' => json_encode(['monday' => '08:00-20:00', 'tuesday' => '08:00-20:00', 'wednesday' => '08:00-20:00', 'thursday' => '08:00-20:00', 'friday' => '08:00-20:00', 'saturday' => '09:00-18:00', 'sunday' => '09:00-18:00']),
                'is_active' => true,
            ])
        ];

        $cafe2Branches = [
            Branch::create([
                'cafe_id' => $cafe2->id,
                'name' => 'Green Valley',
                'slug' => 'green-valley',
                'address' => 'Rr. Ibrahim Rugova 45, Prishtinë',
                'phone' => '+383 44 789 013',
                'opening_hours' => json_encode(['monday' => '08:00-21:00', 'tuesday' => '08:00-21:00', 'wednesday' => '08:00-21:00', 'thursday' => '08:00-21:00', 'friday' => '08:00-21:00', 'saturday' => '08:00-21:00', 'sunday' => '08:00-21:00']),
                'is_active' => true,
            ])
        ];

        $cafe3Branches = [
            Branch::create([
                'cafe_id' => $cafe3->id,
                'name' => 'Downtown Plaza',
                'slug' => 'downtown-plaza',
                'address' => 'Sheshi Nënë Terezë, Prishtinë',
                'phone' => '+383 44 345 679',
                'opening_hours' => json_encode(['monday' => '07:30-23:00', 'tuesday' => '07:30-23:00', 'wednesday' => '07:30-23:00', 'thursday' => '07:30-23:00', 'friday' => '07:30-23:00', 'saturday' => '07:30-23:00', 'sunday' => '07:30-23:00']),
                'is_active' => true,
            ]),
            Branch::create([
                'cafe_id' => $cafe3->id,
                'name' => 'Business District',
                'slug' => 'business-district',
                'address' => 'Rr. Bill Clinton 78, Prishtinë',
                'phone' => '+383 44 345 680',
                'opening_hours' => json_encode(['monday' => '07:00-19:00', 'tuesday' => '07:00-19:00', 'wednesday' => '07:00-19:00', 'thursday' => '07:00-19:00', 'friday' => '07:00-19:00', 'saturday' => '08:00-16:00', 'sunday' => 'closed']),
                'is_active' => true,
            ])
        ];

        // Create staff profiles
        StaffProfile::create([
            'user_id' => $manager->id,
            'cafe_id' => $cafe1->id,
            'branch_id' => null, // Cafe-level access
            'position' => 'manager',
            'is_active' => true,
        ]);

        StaffProfile::create([
            'user_id' => $waiter1->id,
            'cafe_id' => $cafe1->id,
            'branch_id' => $cafe1Branches[0]->id, // Main Street branch
            'position' => 'waiter',
            'is_active' => true,
        ]);

        StaffProfile::create([
            'user_id' => $waiter2->id,
            'cafe_id' => $cafe1->id,
            'branch_id' => $cafe1Branches[1]->id, // University Campus branch
            'position' => 'waiter',
            'is_active' => true,
        ]);

        StaffProfile::create([
            'user_id' => $bartender->id,
            'cafe_id' => $cafe2->id,
            'branch_id' => $cafe2Branches[0]->id, // Green Valley branch
            'position' => 'bartender',
            'is_active' => true,
        ]);

        // Create categories and products for each cafe
        $this->createCafeMenu($cafe1, $cafe1Branches);
        $this->createCafeMenu($cafe2, $cafe2Branches);
        $this->createCafeMenu($cafe3, $cafe3Branches);

        // Create orders with different statuses and payment states
        $this->createSampleOrders($cafe1, $cafe1Branches, $customers);
        $this->createSampleOrders($cafe2, $cafe2Branches, $customers);
        $this->createSampleOrders($cafe3, $cafe3Branches, $customers);
    }

    private function createCafeMenu($cafe, $branches)
    {
        // Create categories
        $categories = [
            Category::create([
                'cafe_id' => $cafe->id,
                'name' => 'Coffee',
                'slug' => 'coffee-' . $cafe->slug,
                'is_active' => true,
            ]),
            Category::create([
                'cafe_id' => $cafe->id,
                'name' => 'Pastries',
                'slug' => 'pastries-' . $cafe->slug,
                'is_active' => true,
            ]),
            Category::create([
                'cafe_id' => $cafe->id,
                'name' => 'Sandwiches',
                'slug' => 'sandwiches-' . $cafe->slug,
                'is_active' => true,
            ]),
            Category::create([
                'cafe_id' => $cafe->id,
                'name' => 'Beverages',
                'slug' => 'beverages-' . $cafe->slug,
                'is_active' => true,
            ]),
        ];

        // Coffee products
        $coffeeProducts = [
            ['name' => 'Espresso', 'price' => 1.50, 'description' => 'Single shot of premium espresso'],
            ['name' => 'Americano', 'price' => 2.00, 'description' => 'Espresso with hot water'],
            ['name' => 'Cappuccino', 'price' => 2.50, 'description' => 'Espresso with steamed milk and foam'],
            ['name' => 'Latte', 'price' => 3.00, 'description' => 'Espresso with steamed milk'],
            ['name' => 'Mocha', 'price' => 3.50, 'description' => 'Espresso with chocolate and steamed milk'],
            ['name' => 'Macchiato', 'price' => 2.80, 'description' => 'Espresso with a dollop of foam'],
            ['name' => 'Flat White', 'price' => 3.20, 'description' => 'Espresso with microfoam milk'],
            ['name' => 'Cold Brew', 'price' => 3.80, 'description' => 'Slow-steeped cold coffee'],
        ];

        // Pastry products
        $pastryProducts = [
            ['name' => 'Croissant', 'price' => 2.00, 'description' => 'Buttery, flaky French pastry'],
            ['name' => 'Chocolate Muffin', 'price' => 2.50, 'description' => 'Rich chocolate muffin with chocolate chips'],
            ['name' => 'Blueberry Scone', 'price' => 2.20, 'description' => 'Traditional scone with fresh blueberries'],
            ['name' => 'Cinnamon Roll', 'price' => 3.00, 'description' => 'Sweet roll with cinnamon and icing'],
            ['name' => 'Danish Pastry', 'price' => 2.80, 'description' => 'Flaky pastry with fruit filling'],
            ['name' => 'Banana Bread', 'price' => 2.60, 'description' => 'Moist banana bread with walnuts'],
            ['name' => 'Cheesecake Slice', 'price' => 4.50, 'description' => 'New York style cheesecake'],
        ];

        // Sandwich products
        $sandwichProducts = [
            ['name' => 'Turkey Club', 'price' => 6.50, 'description' => 'Turkey, bacon, lettuce, tomato on sourdough'],
            ['name' => 'Veggie Wrap', 'price' => 5.50, 'description' => 'Fresh vegetables in a spinach tortilla'],
            ['name' => 'Chicken Panini', 'price' => 7.00, 'description' => 'Grilled chicken with mozzarella and pesto'],
            ['name' => 'Caprese Sandwich', 'price' => 6.00, 'description' => 'Fresh mozzarella, tomato, and basil'],
            ['name' => 'BLT', 'price' => 5.00, 'description' => 'Classic bacon, lettuce, and tomato'],
            ['name' => 'Avocado Toast', 'price' => 4.50, 'description' => 'Smashed avocado on artisan bread'],
            ['name' => 'Grilled Cheese', 'price' => 4.00, 'description' => 'Three cheese blend on sourdough'],
        ];

        // Beverage products
        $beverageProducts = [
            ['name' => 'Fresh Orange Juice', 'price' => 3.00, 'description' => 'Freshly squeezed orange juice'],
            ['name' => 'Green Tea', 'price' => 2.00, 'description' => 'Premium green tea'],
            ['name' => 'Iced Tea', 'price' => 2.50, 'description' => 'Refreshing iced tea'],
            ['name' => 'Smoothie Bowl', 'price' => 4.50, 'description' => 'Acai bowl with fresh fruit'],
            ['name' => 'Sparkling Water', 'price' => 1.50, 'description' => 'Refreshing sparkling water'],
            ['name' => 'Lemonade', 'price' => 2.80, 'description' => 'Fresh squeezed lemonade'],
            ['name' => 'Hot Chocolate', 'price' => 3.20, 'description' => 'Rich hot chocolate with whipped cream'],
        ];

        // Create products for each category
        foreach ($coffeeProducts as $product) {
            Product::create([
                'cafe_id' => $cafe->id,
                'category_id' => $categories[0]->id,
                'name' => $product['name'],
                'slug' => \Illuminate\Support\Str::slug($product['name'] . '-' . $cafe->slug),
                'price' => $product['price'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }

        foreach ($pastryProducts as $product) {
            Product::create([
                'cafe_id' => $cafe->id,
                'category_id' => $categories[1]->id,
                'name' => $product['name'],
                'slug' => \Illuminate\Support\Str::slug($product['name'] . '-' . $cafe->slug),
                'price' => $product['price'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }

        foreach ($sandwichProducts as $product) {
            Product::create([
                'cafe_id' => $cafe->id,
                'category_id' => $categories[2]->id,
                'name' => $product['name'],
                'slug' => \Illuminate\Support\Str::slug($product['name'] . '-' . $cafe->slug),
                'price' => $product['price'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }

        foreach ($beverageProducts as $product) {
            Product::create([
                'cafe_id' => $cafe->id,
                'category_id' => $categories[3]->id,
                'name' => $product['name'],
                'slug' => \Illuminate\Support\Str::slug($product['name'] . '-' . $cafe->slug),
                'price' => $product['price'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }
    }

    private function createSampleOrders($cafe, $branches, $customers)
    {
        $products = Product::where('cafe_id', $cafe->id)->get();
        $statuses = ['pending', 'preparing', 'ready', 'delivered'];
        $paymentStatuses = ['unpaid', 'paid'];

        // Create 15-25 orders per cafe
        for ($i = 0; $i < rand(15, 25); $i++) {
            $customer = $customers->random();
            $branch = $branches[array_rand($branches)];
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            $order = Order::create([
                'user_id' => $customer->id,
                'cafe_id' => $cafe->id,
                'branch_id' => $branch->id,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => rand(0, 1) ? 'cash' : 'card',
                'table_number' => rand(1, 20),
                'total_price' => 0, // Will be calculated
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            // Add 1-4 items to each order
            $itemCount = rand(1, 4);
            $totalPrice = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $lineTotal = $product->price * $quantity;
                $totalPrice += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ]);
            }

            // Update order total
            $order->update(['total_price' => $totalPrice]);

            // Create payment if order is paid
            if ($paymentStatus === 'paid') {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $totalPrice,
                    'method' => $order->payment_method,
                    'status' => 'succeeded',
                    'created_at' => $order->created_at,
                ]);
            }
        }
    }
}
