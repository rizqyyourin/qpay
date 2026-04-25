<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class BuyController extends Controller
{
    public function show(Product $product)
    {
        $product->load('user:id,name');

        return Inertia::render('Buy', [
            'product' => [
                'id'          => $product->id,
                'name'        => $product->name,
                'price'       => $product->price,
                'description' => $product->description,
                'image'       => $product->image,
                'stock'       => $product->stock,
            ],
            'store' => [
                'name' => $product->user->name,
            ],
        ]);
    }
}
