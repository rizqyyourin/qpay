<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductQrController extends Controller
{
    public function show(Product $product): View
    {
        // Ensure user owns this product
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        return view('products.qr', ['product' => $product]);
    }
}
