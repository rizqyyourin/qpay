<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed or find the seller user
        $user = User::updateOrCreate(
            ['email' => 'dev@qpay.co.id'],
            [
                'name' => 'Shop Euy',
                'password' => Hash::make('Qpay123@'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Clean old products and orders for this user to start fresh
        $user->orders()->delete();
        $user->products()->delete();

        // 3. Seed premium products with Unsplash images
        $productsData = [
            [
                'name' => 'Grilled Cheese Burger',
                'price' => 18000,
                'discount' => 3000,
                'stock' => 46,
                'description' => 'Double beef patty, melted cheddar cheese, signature sauce, and toasted brioche bun.',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            [
                'name' => 'Hazelnut Chocolate',
                'price' => 12000,
                'discount' => 0,
                'stock' => 20,
                'description' => 'Premium creamy dark chocolate blend topped with crunchy roasted hazelnuts.',
                'image' => 'https://images.unsplash.com/photo-1511381939415-e44015466834?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            [
                'name' => 'Iced Avocado Coffee',
                'price' => 22000,
                'discount' => 2000,
                'stock' => 15,
                'description' => 'Fresh avocado puree combined with double espresso shot and vanilla ice cream.',
                'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            [
                'name' => 'Red Velvet Cake Slice',
                'price' => 25000,
                'discount' => 5000,
                'stock' => 8,
                'description' => 'Soft red velvet sponge cake layers with velvety smooth cream cheese frosting.',
                'image' => 'https://images.unsplash.com/photo-1616260887585-6457014be0c4?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            [
                'name' => 'Matcha Green Tea Latte',
                'price' => 19000,
                'discount' => 0,
                'stock' => 30,
                'description' => 'Authentic Uji matcha green tea whisked with creamy steamed milk and organic honey.',
                'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            [
                'name' => 'French Fries (Large)',
                'price' => 15000,
                'discount' => 1000,
                'stock' => 50,
                'description' => 'Crispy golden potato fries tossed with sea salt and served with cheese dip.',
                'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=400&h=400&q=80',
            ],
        ];

        $products = [];
        foreach ($productsData as $data) {
            $products[] = Product::create(array_merge($data, ['user_id' => $user->id]));
        }

        // 4. Seed realistic orders across the past 30 days
        $orderCodesUsed = [];
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'confirmed', 'cancelled'];

        // Seed 15 confirmed orders spread over the last month
        for ($i = 0; $i < 15; $i++) {
            $date = now()->subDays(rand(0, 28))->subHours(rand(1, 12));
            
            do {
                $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            } while (in_array($code, $orderCodesUsed));
            $orderCodesUsed[] = $code;

            // Pick 1-3 random products for this order
            $orderProducts = collect($products)->random(rand(1, 3));
            $total = 0;
            
            $order = Order::create([
                'user_id' => $user->id,
                'code' => $code,
                'status' => 'confirmed',
                'total' => 0,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($orderProducts as $prod) {
                $qty = rand(1, 2);
                $effectivePrice = $prod->price - ($prod->discount ?? 0);
                $itemTotal = $effectivePrice * $qty;
                $total += $itemTotal;

                $order->items()->create([
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'price' => $effectivePrice,
                    'qty' => $qty,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $order->update(['total' => $total]);
        }

        // Seed 3 pending orders for today
        for ($j = 0; $j < 3; $j++) {
            $date = now()->subMinutes(rand(5, 120));
            
            do {
                $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            } while (in_array($code, $orderCodesUsed));
            $orderCodesUsed[] = $code;

            $orderProducts = collect($products)->random(rand(1, 2));
            $total = 0;

            $order = Order::create([
                'user_id' => $user->id,
                'code' => $code,
                'status' => 'pending',
                'total' => 0,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($orderProducts as $prod) {
                $qty = rand(1, 2);
                $effectivePrice = $prod->price - ($prod->discount ?? 0);
                $itemTotal = $effectivePrice * $qty;
                $total += $itemTotal;

                $order->items()->create([
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'price' => $effectivePrice,
                    'qty' => $qty,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $order->update(['total' => $total]);
        }
    }
}
