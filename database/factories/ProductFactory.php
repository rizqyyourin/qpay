<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word(),
            'sku' => $this->faker->unique()->ean8(),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(10000, 500000),
            'stock' => $this->faker->numberBetween(10, 100),
        ];
    }
}
