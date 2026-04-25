import { Head, router } from '@inertiajs/react';
import {
    ChevronLeft,
    Image as ImageIcon,
    Loader2,
    Minus,
    Package,
    Plus,
    QrCode,
    ScanLine,
    Trash2,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import { clearCart, getCart, removeFromCart, updateCartQty } from '@/lib/cart';

function formatCurrency(value) {
    return `Rp ${value.toLocaleString('id-ID')}`;
}

export default function Cart() {
    const [cart, setCart] = useState([]);
    const [checkoutState, setCheckoutState] = useState('idle'); // idle | processing
    const [stockError, setStockError] = useState(null);

    useEffect(() => {
        setCart(getCart());
        const handleUpdate = () => setCart(getCart());
        window.addEventListener('qpay-cart-update', handleUpdate);
        return () => window.removeEventListener('qpay-cart-update', handleUpdate);
    }, []);

    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const itemCount = cart.reduce((s, i) => s + i.qty, 0);

    const handleRemove = (id) => setCart(removeFromCart(id));
    const handleQtyChange = (id, qty) => setCart(updateCartQty(id, qty));

    const handleCheckout = () => {
        if (checkoutState !== 'idle' || cart.length === 0) return;
        setCheckoutState('processing');
        setStockError(null);

        router.post(
            route('cart.checkout'),
            { items: cart.map(({ id, qty }) => ({ id, qty })) },
            {
                onSuccess: () => clearCart(),
                onError: (errs) => {
                    setStockError(errs.stock ?? 'Checkout failed. Please try again.');
                    setCheckoutState('idle');
                },
            },
        );
    };

    /* ── Cart screen ── */
    return (
        <>
            <Head title="Cart — qpay" />

            <div className="min-h-screen bg-slate-200 selection:bg-orange-500 selection:text-white">
            <div className="relative mx-auto flex min-h-screen max-w-md flex-col bg-slate-100 pb-36 shadow-xl">
                {/* Sticky header */}
                <header className="sticky top-0 z-50 flex items-center gap-3 bg-orange-500 px-4 py-3 text-white shadow-md">
                    <button
                        type="button"
                        onClick={() =>
                            window.history.length > 1 ? window.history.back() : (window.location.href = '/')
                        }
                        className="shrink-0 rounded-full p-1 transition-colors hover:bg-white/20"
                    >
                        <ChevronLeft className="h-6 w-6" />
                    </button>
                    <span className="flex-1 text-base font-bold">Shopping Cart</span>
                    {itemCount > 0 && (
                        <span className="shrink-0 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-bold">
                            {itemCount} item{itemCount !== 1 ? 's' : ''}
                        </span>
                    )}
                </header>

                {/* Cart items */}
                <div className="mt-2 space-y-2">
                    {cart.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-24">
                            <Package className="mb-4 h-16 w-16 text-slate-200" />
                            <p className="font-bold text-slate-400">Your cart is empty</p>
                            <p className="mt-1 text-sm text-slate-400">Scan a product QR code to get started</p>
                            <div className="mt-5 flex items-center gap-1.5 text-sm font-semibold text-orange-500">
                                <ScanLine className="h-4 w-4" /> Scan a QR code
                            </div>
                        </div>
                    ) : (
                        cart.map((item) => (
                            <div key={item.id} className="bg-white px-4 py-4">
                                <div className="flex gap-3">
                                    {/* Thumbnail */}
                                    <div className="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                        {item.image ? (
                                            <img
                                                src={item.image}
                                                alt={item.name}
                                                className="h-full w-full object-contain p-1"
                                            />
                                        ) : (
                                            <ImageIcon className="h-8 w-8 text-slate-300" />
                                        )}
                                    </div>

                                    {/* Details */}
                                    <div className="flex flex-1 flex-col min-w-0">
                                        <div className="flex items-start justify-between gap-2">
                                            <h3 className="line-clamp-2 text-sm font-bold leading-snug text-slate-900">
                                                {item.name}
                                            </h3>
                                            <button
                                                type="button"
                                                onClick={() => handleRemove(item.id)}
                                                className="shrink-0 text-slate-300 transition-colors hover:text-red-500"
                                                aria-label="Remove"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </button>
                                        </div>
                                        <p className="mt-1 text-base font-black text-orange-500">
                                            {formatCurrency(item.price)}
                                        </p>
                                        {/* Qty stepper */}
                                        <div className="mt-2 flex items-center gap-1.5 self-end rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                                            <button
                                                type="button"
                                                onClick={() => handleQtyChange(item.id, item.qty - 1)}
                                                className="flex h-7 w-7 items-center justify-center rounded-md bg-white text-slate-600 shadow-sm transition-colors hover:bg-red-50 hover:text-red-500"
                                            >
                                                <Minus className="h-3.5 w-3.5" />
                                            </button>
                                            <span className="w-7 text-center text-sm font-black">{item.qty}</span>
                                            <button
                                                type="button"
                                                onClick={() => handleQtyChange(item.id, item.qty + 1)}
                                                className="flex h-7 w-7 items-center justify-center rounded-md bg-white text-slate-600 shadow-sm transition-colors hover:bg-green-50 hover:text-green-500"
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Scan more hint */}
                {cart.length > 0 && (
                    <div className="mt-2 bg-orange-50 px-4 py-3">
                        <div className="flex items-center gap-2 text-orange-600">
                            <ScanLine className="h-4 w-4 shrink-0" />
                            <p className="text-xs font-semibold">
                                Scan more product QR codes to add more items to your cart
                            </p>
                        </div>
                    </div>
                )}

                <p className="mt-4 pb-4 text-center text-xs font-semibold text-slate-400">
                    <QrCode className="mr-1 inline-block h-3.5 w-3.5" />
                    Powered by qpay
                </p>
            </div>

            </div>

            {/* Sticky bottom — order summary + CTA */}
            {cart.length > 0 && (
                <div className="fixed bottom-0 left-1/2 z-50 w-full max-w-md -translate-x-1/2 border-t border-slate-200 bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.12)]">
                    <div className="px-4 pt-3 pb-1">
                        {stockError && (
                            <div className="mb-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-500">
                                {stockError}
                            </div>
                        )}
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium text-slate-500">
                                Subtotal ({itemCount} item{itemCount !== 1 ? 's' : ''})
                            </span>
                            <span className="font-black text-slate-900">{formatCurrency(total)}</span>
                        </div>
                    </div>
                    <div className="border-t border-slate-100 px-4 py-3">
                        <button
                            type="button"
                            onClick={handleCheckout}
                            disabled={checkoutState !== 'idle'}
                            className="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-3.5 text-base font-bold text-white shadow transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {checkoutState === 'processing' ? (
                                <>
                                    <Loader2 className="h-5 w-5 animate-spin" /> Processing...
                                </>
                            ) : (
                                `Place Order · ${formatCurrency(total)}`
                            )}
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
