<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Browsing', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    test('user can browse all products', function () {
        Product::factory(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'barcode',
                        'price',
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ]);
    });

    test('user can search products by name', function () {
        $product = Product::factory()->create([
            'name' => 'Laptop Gaming',
        ]);
        Product::factory(4)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/products/search?q=Laptop');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Laptop Gaming');
    });

    test('user can search products by barcode', function () {
        $product = Product::factory()->create([
            'barcode' => '1234567890123',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/products/barcode/1234567890123');

        $response->assertStatus(200)
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('name', $product->name);
    });

    test('barcode search returns 404 for non-existent barcode', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/products/barcode/9999999999999');

        $response->assertStatus(404);
    });

    test('user can get products by category', function () {
        $category = Category::factory()->create();
        $products = Product::factory(3)->create([
            'category_id' => $category->id,
        ]);
        Product::factory(2)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/products/category/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    test('user can view product details', function () {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('name', $product->name)
            ->assertJsonPath('price', (string) $product->price);
    });

    test('unauthenticated user cannot browse products', function () {
        Product::factory(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    });
});
