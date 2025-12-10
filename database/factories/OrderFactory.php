<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'total_amount' => $this->faker->numberBetween(50000, 500000),
            'discount_amount' => $this->faker->numberBetween(0, 50000),
            'tax_amount' => $this->faker->numberBetween(0, 50000),
            'payment_status' => 'pending',
            'payment_method' => null,
            'notes' => null,
        ];
    }
}
