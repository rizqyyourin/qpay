import { Head, usePage } from '@inertiajs/react';
import {
    ChevronLeft,
    Image as ImageIcon,
    Minus,
    Plus,
    QrCode,
    ScanLine,
    ShoppingCart,
    Store,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import { addToCart, getCart, getCartCount } from '@/lib/cart';

function formatCurrency(value) {
    return `Rp ${value.toLocaleString('id-ID')}`;
}

export default function Buy() {
    const { product, store } = usePage().props;

    const [qty, setQty] = useState(1);
    const [cartCount, setCartCount] = useState(0);
    const [addedState, setAddedState] = useState('idle'); // idle | added

    const alreadyInCart = getCart().find((i) => i.id === product.id)?.qty ?? 0;
    const availableStock = Math.max(product.stock - alreadyInCart, 0);
    const isOutOfStock = availableStock === 0;
    const maxQty = Math.min(availableStock, 99);

    useEffect(() => {
        setCartCount(getCartCount());
        const handleUpdate = () => setCartCount(getCartCount());
        window.addEventListener('qpay-cart-update', handleUpdate);
        return () => window.removeEventListener('qpay-cart-update', handleUpdate);
    }, []);

    const handleAddToCart = () => {
        if (isOutOfStock || addedState === 'added') return;
        addToCart(product, qty);
        window.location.href = '/cart';
    };

    return (
        <>
            <Head title={`${product.name} — ${store.name}`} />

            <div className="min-h-screen bg-slate-200 selection:bg-orange-500 selection:text-white">
            <div className="relative mx-auto flex min-h-screen max-w-md flex-col bg-slate-100 pb-36 shadow-xl">
                {/* Sticky header — Shopee-style orange bar */}
                <header className="sticky top-0 z-50 flex items-center gap-3 bg-orange-500 px-4 py-3 text-white shadow-md">
                    <button
                        type="button"
                        onClick={() => (window.history.length > 1 ? window.history.back() : (window.location.href = '/'))}
                        className="shrink-0 rounded-full p-1 transition-colors hover:bg-white/20"
                    >
                        <ChevronLeft className="h-6 w-6" />
                    </button>
                    <span className="flex-1 truncate text-sm font-bold">{store.name}</span>
                    <a
                        href="/cart"
                        className="relative shrink-0 rounded-full p-1 transition-colors hover:bg-white/20"
                        aria-label="View cart"
                    >
                        <ShoppingCart className="h-6 w-6" />
                        {cartCount > 0 && (
                            <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[10px] font-black text-orange-500">
                                {cartCount > 9 ? '9+' : cartCount}
                            </span>
                        )}
                    </a>
                </header>

                {/* Product image — full width */}
                <div className="relative flex h-64 items-center justify-center bg-white sm:h-80">
                    {product.image ? (
                        <img
                            src={product.image}
                            alt={product.name}
                            className="h-full w-full object-contain p-6"
                        />
                    ) : (
                        <div className="flex flex-col items-center gap-2 text-slate-200">
                            <ImageIcon className="h-16 w-16" />
                            <span className="text-sm font-medium text-slate-300">No image</span>
                        </div>
                    )}
                    {isOutOfStock && (
                        <div className="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[2px]">
                            <span className="rounded-2xl bg-red-500 px-5 py-2.5 text-base font-black text-white shadow-lg">
                                Out of Stock
                            </span>
                        </div>
                    )}
                </div>

                {/* Price + Name */}
                <div className="mt-2 bg-white px-4 py-4">
                    <p className="text-2xl font-black text-orange-500">{formatCurrency(product.price)}</p>
                    <h1 className="mt-1.5 text-xl font-bold leading-snug text-slate-900">{product.name}</h1>
                    <div className="mt-2">
                        <span
                            className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                availableStock === 0
                                    ? 'bg-red-100 text-red-600'
                                    : availableStock <= 5
                                      ? 'bg-amber-100 text-amber-700'
                                      : 'bg-green-100 text-green-700'
                            }`}
                        >
                            {availableStock === 0
                                ? (alreadyInCart > 0 ? 'All stock in cart' : 'Out of stock')
                                : `${availableStock} in stock${alreadyInCart > 0 ? ` (${alreadyInCart} in cart)` : ''}`}
                        </span>
                    </div>
                </div>

                {/* Store info */}
                <div className="mt-2 bg-white px-4 py-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                            <Store className="h-5 w-5 text-orange-500" />
                        </div>
                        <div>
                            <p className="font-bold text-slate-900">{store.name}</p>
                            <p className="text-xs text-slate-400">qpay verified store</p>
                        </div>
                    </div>
                </div>

                {/* Description */}
                {product.description && (
                    <div className="mt-2 bg-white px-4 py-4">
                        <h2 className="mb-2 text-sm font-bold text-slate-900">Product Description</h2>
                        <p className="text-sm leading-relaxed text-slate-500">{product.description}</p>
                    </div>
                )}

                {/* Scan more hint */}
                <div className="mt-2 bg-white px-4 py-3">
                    <div className="flex items-center justify-center gap-2 text-slate-400">
                        <ScanLine className="h-4 w-4 shrink-0" />
                        <p className="text-xs font-medium">
                            Scan another product QR code to add more items to your cart
                        </p>
                    </div>
                </div>

                {/* Powered by */}
                <p className="mt-4 text-center text-xs font-semibold text-slate-400">
                    <QrCode className="mr-1 inline-block h-3.5 w-3.5" />
                    Powered by qpay
                </p>
            </div>
            </div>

            {/* Sticky bottom action bar */}
            <div className="fixed bottom-0 left-1/2 z-50 w-full max-w-md -translate-x-1/2 border-t border-slate-200 bg-white px-4 pt-3 pb-5 shadow-[0_-4px_20px_rgba(0,0,0,0.12)]">
                {/* Qty selector */}
                <div className="mb-3 flex items-center justify-between">
                    <span className="text-sm font-bold text-slate-700">Quantity</span>
                    <div className="flex items-center gap-2 rounded-xl border-2 border-slate-200 bg-slate-50 p-1">
                        <button
                            type="button"
                            onClick={() => setQty((q) => Math.max(1, q - 1))}
                            disabled={qty <= 1 || isOutOfStock}
                            className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm transition-colors hover:bg-red-50 hover:text-red-500 disabled:opacity-40"
                        >
                            <Minus className="h-3.5 w-3.5" />
                        </button>
                        <span className="w-7 text-center text-sm font-black">{qty}</span>
                        <button
                            type="button"
                            onClick={() => setQty((q) => Math.min(maxQty, q + 1))}
                            disabled={qty >= maxQty || isOutOfStock}
                            className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm transition-colors hover:bg-green-50 hover:text-green-500 disabled:opacity-40"
                        >
                            <Plus className="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                {/* Action buttons */}
                <div className="flex gap-2">
                    <a
                        href="/cart"
                        className="flex flex-1 items-center justify-center gap-1.5 rounded-xl border-2 border-orange-500 py-3 text-sm font-bold text-orange-500 transition-colors hover:bg-orange-50"
                    >
                        <ShoppingCart className="h-4 w-4" />
                        Cart{cartCount > 0 ? ` (${cartCount})` : ''}
                    </a>
                    <button
                        type="button"
                        onClick={handleAddToCart}
                        disabled={isOutOfStock}
                        className={`flex flex-[2] items-center justify-center gap-1.5 rounded-xl py-3 text-sm font-bold text-white transition-all disabled:cursor-not-allowed disabled:opacity-50 ${
                            addedState === 'added' ? 'bg-green-500' : 'bg-orange-500 hover:bg-orange-600'
                        }`}
                    >
                        {addedState === 'added' ? (
                            '✓ Added to Cart'
                        ) : isOutOfStock ? (
                            'Out of Stock'
                        ) : (
                            <>
                                <ShoppingCart className="h-4 w-4" /> Add to Cart
                            </>
                        )}
                    </button>
                </div>
            </div>
        </>
    );
}
