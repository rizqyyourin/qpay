<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->numberBetween(50000, 500000),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'transfer', 'ewallet']),
            'status' => 'completed',
            'transaction_id' => $this->faker->unique()->ean13(),
            'gateway_response' => null,
        ];
    }
}
