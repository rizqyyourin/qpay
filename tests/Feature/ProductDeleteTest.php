<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_soft_delete_their_own_product(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Test Product',
            'price' => 1000,
            'stock' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('products.destroy', $product));

        $response->assertRedirect(route('dashboard'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_user_cannot_delete_another_users_product(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $product = Product::create([
            'user_id' => $owner->id,
            'name' => 'Owner Product',
            'price' => 1000,
            'stock' => 3,
        ]);

        $this
            ->actingAs($attacker)
            ->delete(route('products.destroy', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}