import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    ExternalLink,
    Image as ImageIcon,
    Loader2,
    LogOut,
    Package,
    Plus,
    Printer,
    QrCode,
    Receipt,
    RefreshCw,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    Ticket,
    Timer,
    Trash2,
    TrendingUp,
    UploadCloud,
    X,
    XCircle,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

import { generateProductAssets } from '@/lib/qpay-ai';

function formatCurrency(value) {
    return `Rp ${value.toLocaleString('id-ID')}`;
}

export default function Dashboard() {
    const { auth, products, ai_enabled: serverAiEnabled, pending_orders: serverPendingOrders, monthly_revenue: monthlyRevenue = 0, monthly_orders: monthlyOrders = 0 } = usePage().props;
    const user = auth.user;
    const [aiEnabled, setAiEnabled] = useState(serverAiEnabled ?? false);
    const [aiToggling, setAiToggling] = useState(false);

    const [storeName, setStoreName] = useState(user.name);
    const [editingName, setEditingName] = useState(false);
    const storeNameRef = useRef(null);

    const handleNameClick = () => {
        setEditingName(true);
        setTimeout(() => storeNameRef.current?.select(), 0);
    };

    const handleNameSave = () => {
        const trimmed = storeName.trim();
        if (!trimmed) { setStoreName(user.name); setEditingName(false); return; }
        setEditingName(false);
        if (trimmed === user.name) return;
        router.patch(route('settings.store-name'), { name: trimmed }, { preserveScroll: true });
    };

    const handleNameKeyDown = (e) => {
        if (e.key === 'Enter') { e.preventDefault(); storeNameRef.current?.blur(); }
        if (e.key === 'Escape') { setStoreName(user.name); setEditingName(false); }
    };

    const [view, setView] = useState('seller');
    const [cart, setCart] = useState([]);
    const [showQRFor, setShowQRFor] = useState(null);
    const [checkoutState, setCheckoutState] = useState('idle');
    const [promoInput, setPromoInput] = useState('');
    const [appliedPromo, setAppliedPromo] = useState(null);
    const [promoMessage, setPromoMessage] = useState('');
    const [isAddingProduct, setIsAddingProduct] = useState(false);
    const [priceInput, setPriceInput] = useState('');
    const [stockInput, setStockInput] = useState('');
    const [descriptionInput, setDescriptionInput] = useState('');
    const [regeneratingId, setRegeneratingId] = useState(null);
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const fileInputRef = useRef(null);

    // Pending orders
    const [pendingOrders, setPendingOrders] = useState(serverPendingOrders ?? []);
    const [confirmingId, setConfirmingId] = useState(null);
    const [cancellingId, setCancellingId] = useState(null);
    const [orderModal, setOrderModal] = useState(null);
    const [confirmedOrder, setConfirmedOrder] = useState(null);
    const [countdown, setCountdown] = useState(3);
    const countdownRef = useRef(null);

    // Auto-poll pending orders every 6 seconds
    useEffect(() => {
        const timer = setInterval(() => {
            router.reload({ only: ['pending_orders'], preserveScroll: true, onSuccess: (page) => {
                setPendingOrders(page.props.pending_orders ?? []);
            }});
        }, 6000);
        return () => clearInterval(timer);
    }, []);

    // Sync when Inertia updates props (e.g. after confirm/cancel)
    useEffect(() => {
        setPendingOrders(serverPendingOrders ?? []);
    }, [serverPendingOrders]);

    const handleConfirmOrder = (orderId, order = null) => {
        setConfirmingId(orderId);
        router.post(route('orders.confirm', orderId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (order) {
                    setConfirmedOrder(order);
                    setCountdown(3);
                    clearInterval(countdownRef.current);
                    countdownRef.current = setInterval(() => {
                        setCountdown((c) => {
                            if (c <= 1) {
                                clearInterval(countdownRef.current);
                                setConfirmedOrder(null);
                                return 3;
                            }
                            return c - 1;
                        });
                    }, 1000);
                }
            },
            onFinish: () => setConfirmingId(null),
        });
    };

    const handleCancelOrder = (orderId) => {
        setCancellingId(orderId);
        router.post(route('orders.cancel', orderId), {}, {
            preserveScroll: true,
            onFinish: () => setCancellingId(null),
        });
    };

    const subtotal = useMemo(
        () => cart.reduce((sum, item) => sum + item.price * item.qty, 0),
        [cart],
    );

    const discount = useMemo(() => {
        if (!appliedPromo || subtotal <= 0) return 0;
        if (appliedPromo.type === 'percent') return subtotal * (appliedPromo.value / 100);
        return Math.min(appliedPromo.value, subtotal);
    }, [appliedPromo, subtotal]);

    const finalTotal = subtotal - discount;

    const handleAiToggle = () => {
        const newValue = !aiEnabled;
        setAiEnabled(newValue);
        setAiToggling(true);
        router.patch(route('settings.ai-toggle'), { ai_enabled: newValue }, {
            preserveScroll: true,
            onFinish: () => setAiToggling(false),
        });
    };

    const handlePriceChange = (event) => {
        const rawValue = event.target.value.replace(/\D/g, '');
        if (!rawValue) { setPriceInput(''); return; }
        setPriceInput(Number(rawValue).toLocaleString('id-ID'));
    };

    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    const handleImageFileChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
            alert('Only JPG, PNG, WEBP, or GIF images are allowed.');
            e.target.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be under 5 MB.');
            e.target.value = '';
            return;
        }
        setImageFile(file);
        setImagePreview(URL.createObjectURL(file));
    };

    const handleClearImage = () => {
        setImageFile(null);
        setImagePreview(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const handleAddProduct = async (event) => {
        event.preventDefault();

        const form = event.target;
        const name = form.productName.value.trim();
        const price = Number.parseInt(priceInput.replace(/\D/g, ''), 10);
        const stock = Number.parseInt(stockInput, 10);

        if (!name || !price || Number.isNaN(stock)) return;

        setIsAddingProduct(true);

        let description = null;
        let image = null;

        // User-uploaded file takes priority over AI
        if (imageFile) {
            router.post(route('products.store'), {
                name,
                price,
                stock,
                description: descriptionInput.trim() || null,
                image_file: imageFile,
            }, {
                forceFormData: true,
                onFinish: () => {
                    form.reset();
                    setPriceInput('');
                    setStockInput('');
                    setDescriptionInput('');
                    setImageFile(null);
                    setImagePreview(null);
                    setIsAddingProduct(false);
                },
            });
            return;
        }

        if (aiEnabled) {
            const generated = await generateProductAssets(name);
            description = generated.description;
            image = generated.image;
        }

        // Manual description overrides AI if provided
        if (descriptionInput.trim()) {
            description = descriptionInput.trim();
        }

        router.post(route('products.store'), {
            name,
            price,
            stock,
            description,
            image,
        }, {
            onFinish: () => {
                form.reset();
                setPriceInput('');
                setStockInput('');
                setDescriptionInput('');
                setIsAddingProduct(false);
            },
        });
    };

    const handleRegenerateProduct = async (product) => {
        if (!aiEnabled) return;
        setRegeneratingId(product.id);

        const variations = ['different angle', 'close up shot', 'creative composition', 'front view'];
        const generated = await generateProductAssets(product.name, {
            variation: variations[Math.floor(Math.random() * variations.length)],
        });

        router.patch(route('products.update', product.id), {
            description: generated.description || product.description,
            image: generated.image ?? product.image,
        }, {
            onFinish: () => setRegeneratingId(null),
        });
    };

    const handleDeleteProduct = (id) => {
        router.delete(route('products.destroy', id));
    };

    const handlePrintQR = (product) => {
        const url = `${window.location.origin}/buy/${product.id}`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html>
<html><head><title>QR — ${product.name}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
  .card { border: 3px solid #000; border-radius: 20px; padding: 32px 28px; text-align: center; max-width: 320px; width: 100%; }
  .brand { font-size: 11px; font-weight: 700; color: #f97316; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 16px; }
  img { width: 200px; height: 200px; }
  h2 { margin: 14px 0 4px; font-size: 20px; font-weight: 900; }
  .price { font-size: 22px; font-weight: 800; color: #f97316; margin: 4px 0 12px; }
  .hint { font-size: 12px; color: #64748b; font-weight: 600; }
  @media print { body { justify-content: flex-start; align-items: flex-start; } }
</style>
</head><body>
<div class="card">
  <div class="brand">qpay</div>
  <img src="${qrUrl}" alt="QR Code" />
  <h2>${product.name}</h2>
  <div class="price">Rp ${product.price.toLocaleString('id-ID')}</div>
  <div class="hint">📱 Scan to buy</div>
</div>
<script>window.onload = () => window.print();<\/script>
</body></html>`);
        win.document.close();
    };

    const handleSimulateScan = (product) => {
        setCart((currentCart) => {
            const existingItem = currentCart.find((item) => item.id === product.id);
            if (existingItem) {
                return currentCart.map((item) =>
                    item.id === product.id ? { ...item, qty: item.qty + 1 } : item,
                );
            }
            return [...currentCart, { ...product, qty: 1 }];
        });

        setAppliedPromo(null);
        setPromoInput('');
        setPromoMessage('');
        setView('buyer');
        setShowQRFor(null);
    };

    const handleDecrementQty = (productId) => {
        setCart((currentCart) => {
            const item = currentCart.find((entry) => entry.id === productId);
            if (!item) return currentCart;
            if (item.qty === 1) return currentCart.filter((entry) => entry.id !== productId);
            return currentCart.map((entry) =>
                entry.id === productId ? { ...entry, qty: entry.qty - 1 } : entry,
            );
        });
    };

    const handleIncrementQty = (productId) => {
        setCart((currentCart) =>
            currentCart.map((entry) =>
                entry.id === productId ? { ...entry, qty: entry.qty + 1 } : entry,
            ),
        );
    };

    const handleApplyPromo = () => {
        const code = promoInput.toUpperCase().trim();
        if (!code) return;

        if (code === 'SAVE20') {
            setAppliedPromo({ code: 'SAVE20', type: 'percent', value: 20 });
            setPromoMessage('✨ Coupon applied! 20% discount');
            return;
        }

        if (code === 'OFF10K') {
            setAppliedPromo({ code: 'OFF10K', type: 'flat', value: 10000 });
            setPromoMessage('✨ Coupon applied! Rp 10,000 off');
            return;
        }

        setAppliedPromo(null);
        setPromoMessage('Coupon not found or expired.');
    };

    const handleRemovePromo = () => {
        setAppliedPromo(null);
        setPromoInput('');
        setPromoMessage('');
    };

    const handleCheckout = async () => {
        setCheckoutState('processing');

        try {
            await fetch(route('checkout'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({
                    items: cart.map((item) => ({ id: item.id, qty: item.qty })),
                }),
            });
        } catch {
            // stock update is best-effort in simulation
        }

        window.setTimeout(() => {
            setCheckoutState('success');

            window.setTimeout(() => {
                setCart([]);
                setAppliedPromo(null);
                setPromoInput('');
                setPromoMessage('');
                setView('seller');
                setCheckoutState('idle');
                router.reload({ only: ['products'] });
            }, 3500);
        }, 1500);
    };

    if (view === 'buyer') {
        return (
            <>
                <Head title="qpay Checkout" />

                <div className="flex min-h-screen flex-col items-center justify-center bg-slate-900 p-4 selection:bg-orange-500 selection:text-white">
                    <div className="relative flex h-[700px] w-full max-w-sm flex-col overflow-hidden rounded-[2.5rem] border-[6px] border-black bg-white shadow-2xl">
                        {checkoutState === 'success' ? (
                            <div className="absolute inset-0 z-50 flex flex-col items-center justify-center bg-black p-6 animate-in fade-in duration-300">
                                <div className="flex w-full flex-col items-center animate-slide-up-fade">
                                    <div className="mb-8 flex h-24 w-24 rotate-3 items-center justify-center rounded-[2rem] bg-orange-500 shadow-[0_8px_0_0_#c2410c]">
                                        <CheckCircle2 className="h-12 w-12 -rotate-3 text-black" strokeWidth={2.5} />
                                    </div>
                                    <h2 className="mb-3 text-4xl font-black tracking-tight text-white">
                                        Payment Complete!
                                    </h2>
                                    <p className="mb-10 text-center text-lg font-medium text-slate-400">
                                        {formatCurrency(finalTotal)} paid.
                                    </p>
                                    <div className="w-full max-w-[280px] rounded-[2rem] border-2 border-slate-800 bg-slate-900 p-6">
                                        <div className="mb-4 flex items-center justify-between border-b-2 border-dashed border-slate-800 pb-4">
                                            <span className="text-sm font-bold text-slate-500">Merchant</span>
                                            <span className="text-sm font-bold text-white">{user.name}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-bold text-slate-500">Status</span>
                                            <span className="flex items-center gap-1.5 text-sm font-black text-orange-500">
                                                <CheckCircle2 className="h-4 w-4" /> Paid
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : null}

                        <div className="relative shrink-0 bg-orange-500 p-6 text-center text-white">
                            <button
                                type="button"
                                onClick={() => setView('seller')}
                                disabled={checkoutState !== 'idle'}
                                className="absolute left-4 top-6 text-white/80 transition hover:text-white disabled:opacity-50"
                            >
                                <ArrowLeft className="h-6 w-6" />
                            </button>
                            <h2 className="mt-1 text-xl font-bold">qpay Cart</h2>
                            <p className="text-sm font-medium text-orange-100">{user.name}</p>
                        </div>

                        <div className="relative flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4">
                            {checkoutState === 'processing' ? (
                                <div className="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 backdrop-blur-sm">
                                    <Loader2 className="mb-4 h-10 w-10 animate-spin text-orange-500" />
                                    <p className="font-bold text-slate-800">Processing Payment...</p>
                                </div>
                            ) : null}

                            {cart.length === 0 ? (
                                <div className="flex h-full flex-col items-center justify-center text-slate-400">
                                    <ShoppingCart className="mb-2 h-12 w-12 opacity-50" />
                                    <p className="font-bold">Cart is empty</p>
                                </div>
                            ) : (
                                cart.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm animate-in slide-in-from-right-4 duration-300"
                                    >
                                        <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                            {item.image ? (
                                                <img
                                                    src={item.image}
                                                    alt={item.name}
                                                    className="h-full w-full object-cover mix-blend-multiply"
                                                />
                                            ) : (
                                                <ImageIcon className="h-6 w-6 text-slate-300" />
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <h4 className="line-clamp-1 text-sm font-bold leading-tight text-black">
                                                {item.name}
                                            </h4>
                                            <p className="mt-0.5 text-sm font-bold text-orange-500">
                                                {formatCurrency(item.price)}
                                            </p>
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                            <button
                                                type="button"
                                                onClick={() => handleDecrementQty(item.id)}
                                                className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-lg leading-none text-slate-600 shadow-sm transition-colors hover:bg-red-50 hover:text-red-500"
                                            >
                                                -
                                            </button>
                                            <span className="w-4 text-center text-sm font-bold">{item.qty}</span>
                                            <button
                                                type="button"
                                                onClick={() => handleIncrementQty(item.id)}
                                                className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-lg leading-none text-slate-600 shadow-sm transition-colors hover:bg-green-50 hover:text-green-500"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}

                            {cart.length > 0 ? (
                                <div
                                    className="mt-4 flex cursor-pointer flex-col items-center p-4 opacity-50 transition-opacity hover:opacity-100"
                                    onClick={() => setView('seller')}
                                >
                                    <div className="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-200">
                                        <ArrowRight className="h-4 w-4 rotate-90 text-slate-500" />
                                    </div>
                                    <p className="text-center text-xs font-bold text-slate-500">
                                        Go back to scan more products
                                    </p>
                                </div>
                            ) : null}
                        </div>

                        {cart.length > 0 ? (
                            <div className="shrink-0 border-t-2 border-slate-100 bg-white px-6 pb-2 pt-4">
                                <div className="mb-1 flex items-center gap-2">
                                    <Ticket className="h-5 w-5 text-slate-400" />
                                    <span className="text-sm font-bold text-slate-700">Have a Coupon?</span>
                                </div>
                                <div className="mt-2 flex gap-2">
                                    <input
                                        type="text"
                                        value={promoInput}
                                        onChange={(event) => setPromoInput(event.target.value)}
                                        disabled={appliedPromo !== null || checkoutState !== 'idle'}
                                        placeholder="Enter SAVE20..."
                                        className="flex-1 rounded-xl border-2 border-slate-200 px-4 py-3 text-sm font-bold uppercase text-slate-700 outline-none focus:border-orange-500 disabled:bg-slate-50"
                                    />
                                    {appliedPromo ? (
                                        <button
                                            type="button"
                                            onClick={handleRemovePromo}
                                            disabled={checkoutState !== 'idle'}
                                            className="rounded-xl bg-red-50 px-5 py-3 text-sm font-bold text-red-500 transition-colors hover:bg-red-100"
                                        >
                                            Remove
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={handleApplyPromo}
                                            disabled={!promoInput.trim() || checkoutState !== 'idle'}
                                            className="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-orange-500 disabled:opacity-50"
                                        >
                                            Apply
                                        </button>
                                    )}
                                </div>
                                {promoMessage ? (
                                    <p className={`mt-2 text-xs font-bold ${appliedPromo ? 'text-green-500' : 'text-red-500'}`}>
                                        {promoMessage}
                                    </p>
                                ) : null}
                            </div>
                        ) : null}

                        <div className="relative z-20 shrink-0 border-t border-dashed border-slate-200 bg-white p-6">
                            {appliedPromo ? (
                                <div className="mb-1 flex items-center justify-between animate-in slide-in-from-top-2">
                                    <span className="text-sm font-medium text-slate-500">Subtotal</span>
                                    <span className="text-sm font-medium text-slate-500">{formatCurrency(subtotal)}</span>
                                </div>
                            ) : null}
                            {appliedPromo ? (
                                <div className="mb-3 flex items-center justify-between animate-in slide-in-from-top-2">
                                    <span className="text-sm font-bold text-orange-500">Coupon ({appliedPromo.code})</span>
                                    <span className="text-sm font-bold text-orange-500">- {formatCurrency(discount)}</span>
                                </div>
                            ) : null}
                            <div className="mb-4 flex items-center justify-between">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-2xl font-bold text-black">{formatCurrency(finalTotal)}</span>
                            </div>
                            <button
                                type="button"
                                onClick={handleCheckout}
                                disabled={cart.length === 0 || checkoutState !== 'idle'}
                                className="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-black py-4 text-lg font-bold text-white shadow-lg transition-colors hover:bg-orange-500 disabled:opacity-50 disabled:hover:bg-black"
                            >
                                <div className="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-[shimmer_1.5s_infinite]" />
                                {checkoutState === 'processing' ? (
                                    <>
                                        <Loader2 className="h-5 w-5 animate-spin" /> Processing...
                                    </>
                                ) : (
                                    <>
                                        <Receipt className="h-5 w-5" /> Pay Now
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="qpay Dashboard" />

            <div className="flex h-screen flex-col overflow-hidden bg-slate-100 selection:bg-orange-500 selection:text-white">
                {/* Top Navbar */}
                <nav className="z-40 shrink-0 bg-black px-6 py-3 text-white shadow-md">
                    <div className="flex items-center justify-between">
                        <Link href="/" className="flex items-center gap-2.5">
                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500">
                                <QrCode className="h-5 w-5 text-white" />
                            </div>
                            <span className="text-lg font-bold">
                                qpay <span className="text-orange-500">Dashboard</span>
                            </span>
                        </Link>
                        <div className="flex items-center gap-3">
                            <div className="hidden items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 sm:flex">
                                <div className="h-2 w-2 shrink-0 rounded-full bg-green-400" />
                                {editingName ? (
                                    <input
                                        ref={storeNameRef}
                                        value={storeName}
                                        onChange={(e) => setStoreName(e.target.value)}
                                        onBlur={handleNameSave}
                                        onKeyDown={handleNameKeyDown}
                                        maxLength={255}
                                        className="w-32 bg-slate-700 text-sm font-semibold text-white outline-none rounded px-1"
                                    />
                                ) : (
                                    <span
                                        className="cursor-text text-sm font-semibold hover:text-orange-400 transition-colors"
                                        onClick={handleNameClick}
                                        title="Click to rename your store"
                                    >
                                        {storeName}
                                    </span>
                                )}
                            </div>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="rounded-lg bg-slate-800 p-2 transition-colors hover:bg-red-500"
                                title="Sign Out"
                            >
                                <LogOut className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </nav>

                {/* Body: Sidebar + Main */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Sidebar */}
                    <aside className="flex w-72 shrink-0 flex-col overflow-y-auto border-r border-slate-200 bg-white xl:w-80">
                        {/* Add Product */}
                        <div className="border-b border-slate-100 p-5">
                            <h3 className="mb-4 flex items-center gap-2 text-base font-bold text-black">
                                <div className="flex h-6 w-6 items-center justify-center rounded-md bg-orange-500">
                                    <Plus className="h-4 w-4 text-white" />
                                </div>
                                Add Product
                            </h3>
                            <form onSubmit={handleAddProduct} className="space-y-3">
                                <div>
                                    <label className="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                        Product Name*
                                    </label>
                                    <input
                                        required
                                        name="productName"
                                        disabled={isAddingProduct}
                                        type="text"
                                        placeholder="e.g.: Grilled Cheese Burger"
                                        className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none transition-colors focus:border-orange-500 disabled:opacity-50"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <label className="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                            Price (Rp)*
                                        </label>
                                        <input
                                            required
                                            name="productPrice"
                                            value={priceInput}
                                            onChange={handlePriceChange}
                                            disabled={isAddingProduct}
                                            type="text"
                                            placeholder="25,000"
                                            className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none transition-colors focus:border-orange-500 disabled:opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                            Stock*
                                        </label>
                                        <input
                                            required
                                            name="productStock"
                                            value={stockInput}
                                            onChange={(e) => setStockInput(e.target.value.replace(/\D/g, ''))}
                                            disabled={isAddingProduct}
                                            type="text"
                                            inputMode="numeric"
                                            placeholder="50"
                                            className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none transition-colors focus:border-orange-500 disabled:opacity-50"
                                        />
                                    </div>
                                </div>
                                {/* Optional description */}
                                <div>
                                    <label className="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                        Description
                                    </label>
                                    <textarea
                                        name="productDescription"
                                        value={descriptionInput}
                                        onChange={(e) => setDescriptionInput(e.target.value)}
                                        disabled={isAddingProduct}
                                        rows={2}
                                        placeholder="Short product description..."
                                        className="w-full resize-none rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none transition-colors focus:border-orange-500 disabled:opacity-50"
                                    />
                                    {aiEnabled && !descriptionInput.trim() && (
                                        <p className="mt-1 text-xs text-slate-400">Leave blank to auto-generate with AI.</p>
                                    )}
                                </div>
                                {/* Optional image upload */}
                                <div>
                                    <label className="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                        Product Image <span className="normal-case font-medium text-slate-400">(max 5 MB)</span>
                                    </label>
                                    {imagePreview ? (
                                        <div className="relative overflow-hidden rounded-xl border-2 border-slate-200 bg-white">
                                            <img
                                                src={imagePreview}
                                                alt="Preview"
                                                className="h-32 w-full object-contain p-2"
                                            />
                                            <button
                                                type="button"
                                                onClick={handleClearImage}
                                                className="absolute right-2 top-2 rounded-full bg-black/60 p-1 text-white hover:bg-red-500"
                                            >
                                                <X className="h-3 w-3" />
                                            </button>
                                        </div>
                                    ) : (
                                        <label className="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 py-4 transition-colors hover:border-orange-400 hover:bg-orange-50">
                                            <UploadCloud className="h-6 w-6 text-slate-400" />
                                            <span className="text-xs font-semibold text-slate-400">Click to upload image</span>
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp,image/gif"
                                                className="hidden"
                                                disabled={isAddingProduct}
                                                onChange={handleImageFileChange}
                                            />
                                        </label>
                                    )}
                                    {imageFile && aiEnabled && (
                                        <p className="mt-1 text-xs text-slate-400">AI generation will be skipped since you uploaded an image.</p>
                                    )}
                                </div>
                                <button
                                    type="submit"
                                    disabled={isAddingProduct}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-2.5 text-sm font-bold text-black shadow-[0_3px_0_0_rgba(0,0,0,0.2)] transition-all hover:bg-black hover:text-white disabled:opacity-70 disabled:shadow-none"
                                >
                                    {isAddingProduct ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" /> Adding...
                                        </>
                                    ) : (
                                        'Save Product'
                                    )}
                                </button>
                            </form>
                        </div>

                        {/* Stats */}
                        <div className="border-b border-slate-100 p-5">
                            <p className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Overview</p>
                            <div className="space-y-2">
                                {/* Active products */}
                                <div className="flex items-center justify-between rounded-2xl bg-black p-4 text-white">
                                    <div>
                                        <p className="text-xs font-semibold text-slate-400">Active Products</p>
                                        <p className="text-3xl font-black text-white">{products.length}</p>
                                        <p className="text-xs font-medium text-orange-400">items for sale</p>
                                    </div>
                                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500">
                                        <Package className="h-6 w-6 text-black" />
                                    </div>
                                </div>

                                {/* Monthly revenue */}
                                <div className="flex items-center justify-between rounded-2xl bg-orange-500 p-4 text-white">
                                    <div>
                                        <p className="text-xs font-semibold text-orange-100">Revenue this month</p>
                                        <p className="text-xl font-black leading-tight">{formatCurrency(monthlyRevenue)}</p>
                                    </div>
                                    <TrendingUp className="h-8 w-8 text-white/60" />
                                </div>

                                {/* Monthly orders */}
                                <div className="flex items-center justify-between rounded-2xl bg-slate-100 p-4">
                                    <div>
                                        <p className="text-xs font-semibold text-slate-400">Orders this month</p>
                                        <p className="text-3xl font-black text-slate-800">{monthlyOrders}</p>
                                        <p className="text-xs font-medium text-slate-400">confirmed transactions</p>
                                    </div>
                                    <ShoppingBag className="h-8 w-8 text-slate-300" />
                                </div>

                                {/* Low stock warning */}
                                {products.some((p) => p.stock <= 5) && (
                                    <div className="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3">
                                        <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                                        <div className="min-w-0">
                                            <p className="text-xs font-bold text-amber-700">Low Stock Warning</p>
                                            <p className="text-xs text-amber-600">
                                                {products
                                                    .filter((p) => p.stock <= 5)
                                                    .map((p) => `${p.name} (${p.stock})`)
                                                    .join(', ')}
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {/* Stock per product */}
                                {products.length > 0 && (
                                    <div className="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                        <p className="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                                            Stock per Product
                                        </p>
                                        <div className="space-y-1.5">
                                            {products.map((p) => (
                                                <div key={p.id} className="flex items-center justify-between gap-2">
                                                    <span className="min-w-0 truncate text-xs font-medium text-slate-600">
                                                        {p.name}
                                                    </span>
                                                    <span
                                                        className={`shrink-0 text-xs font-black ${
                                                            p.stock === 0
                                                                ? 'text-red-500'
                                                                : p.stock <= 5
                                                                  ? 'text-amber-500'
                                                                  : 'text-green-600'
                                                        }`}
                                                    >
                                                        {p.stock} units
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* AI Toggle */}
                        <div className="p-5">
                            <p className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Settings</p>
                            <div className="rounded-2xl border-2 border-slate-100 p-4">
                                <div className="flex items-center justify-between gap-3">
                                    <div className="flex items-center gap-3">
                                        <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors ${aiEnabled ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-400'}`}>
                                            <Sparkles className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-black">AI Features</p>
                                            <p className="text-xs text-slate-400">Auto-generate images</p>
                                        </div>
                                    </div>
                                    {/* Toggle pill */}
                                    <button
                                        type="button"
                                        role="switch"
                                        aria-checked={aiEnabled}
                                        onClick={handleAiToggle}
                                        disabled={aiToggling}
                                        className={`relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 ${aiEnabled ? 'bg-orange-500' : 'bg-slate-200'}`}
                                    >
                                        <span
                                            className={`inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-200 ease-in-out ${aiEnabled ? 'translate-x-5' : 'translate-x-0'}`}
                                        />
                                    </button>
                                </div>
                                <p className={`mt-3 rounded-lg px-3 py-2 text-xs font-medium ${aiEnabled ? 'bg-orange-50 text-orange-700' : 'bg-slate-50 text-slate-500'}`}>
                                    {aiEnabled
                                        ? 'AI is on — images & descriptions will be generated automatically.'
                                        : 'AI is off — products saved without generated content.'}
                                </p>
                            </div>
                        </div>
                    </aside>

                    {/* Main content */}
                    <main className="flex-1 overflow-y-auto p-6 lg:p-8">

                        {/* Pending Orders */}
                        {pendingOrders.length > 0 && (
                            <div className="mb-8">
                                <div className="mb-3 flex items-center gap-2">
                                    <div className="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500">
                                        <Timer className="h-3.5 w-3.5 text-white" />
                                    </div>
                                    <h2 className="text-base font-bold text-black">Pending Orders</h2>
                                    <span className="animate-pulse rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                        {pendingOrders.length} waiting
                                    </span>
                                </div>
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    {pendingOrders.map((order) => (
                                        <button
                                            key={order.id}
                                            type="button"
                                            onClick={() => setOrderModal(order)}
                                            className="group flex flex-col gap-2 rounded-2xl border-2 border-amber-200 bg-white p-4 text-left shadow-sm transition-all hover:border-amber-400 hover:shadow-md active:scale-[0.98]"
                                        >
                                            <div className="flex items-center justify-between">
                                                <span className="font-mono text-xl font-black tracking-widest text-slate-900">
                                                    {order.code}
                                                </span>
                                                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100">
                                                    <Timer className="h-3.5 w-3.5 text-amber-600" />
                                                </span>
                                            </div>
                                            <p className="text-base font-black text-orange-500">
                                                {formatCurrency(order.total)}
                                            </p>
                                            <p className="line-clamp-1 text-xs text-slate-400">
                                                {order.items.map((i) => `${i.name} ×${i.qty}`).join(', ')}
                                            </p>
                                            <p className="text-xs font-semibold text-amber-600 group-hover:text-amber-700">
                                                Tap to review →
                                            </p>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        <div className="mb-6 flex items-center justify-between">
                            <h2 className="text-2xl font-bold text-black">Product Catalog &amp; QR Codes</h2>
                            <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-slate-500 shadow-sm">
                                {products.length} {products.length === 1 ? 'product' : 'products'}
                            </span>
                        </div>
                        {products.length === 0 ? (
                            <div className="flex h-64 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white">
                                <Package className="mb-3 h-10 w-10 text-slate-300" />
                                <p className="font-bold text-slate-400">No products yet.</p>
                                <p className="text-sm text-slate-400">Add your first product using the sidebar.</p>
                            </div>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {products.map((product) => (
                                    <div
                                        key={product.id}
                                        className="group relative flex flex-col overflow-hidden rounded-[1.5rem] border-2 border-slate-100 bg-white shadow-sm"
                                    >
                                        <div className="relative flex h-44 items-center justify-center overflow-hidden border-b border-slate-100 bg-white">
                                            {regeneratingId === product.id ? (
                                                <div className="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm animate-in fade-in">
                                                    <Loader2 className="mb-2 h-8 w-8 animate-spin text-orange-500" />
                                                    <span className="text-sm font-bold text-slate-800">Regenerating...</span>
                                                </div>
                                            ) : null}

                                            {product.image ? (
                                                <img
                                                    src={product.image}
                                                    alt={product.name}
                                                    className="h-full w-full object-contain p-2"
                                                />
                                            ) : (
                                                <div className="flex flex-col items-center gap-2 text-slate-200">
                                                    <ImageIcon className="h-12 w-12" />
                                                    <span className="text-xs font-semibold text-slate-300">No image</span>
                                                </div>
                                            )}

                                            <div className="absolute right-3 top-3 z-10 flex gap-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                {aiEnabled ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRegenerateProduct(product)}
                                                        disabled={regeneratingId === product.id}
                                                        className="rounded-xl border border-slate-100 bg-white p-2 text-slate-400 shadow-md transition-colors hover:text-blue-500"
                                                        title="Regenerate with AI"
                                                    >
                                                        <RefreshCw className="h-4 w-4" />
                                                    </button>
                                                ) : null}
                                                <button
                                                    type="button"
                                                    onClick={() => handleDeleteProduct(product.id)}
                                                    disabled={regeneratingId === product.id}
                                                    className="rounded-xl border border-slate-100 bg-white p-2 text-slate-400 shadow-md transition-colors hover:text-red-500"
                                                    title="Delete Product"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>

                                        <div className="flex flex-1 flex-col p-5">
                                            <h4 className="mb-1 line-clamp-1 text-lg font-bold text-black">
                                                {product.name}
                                            </h4>
                                            <p className="mb-1 text-xl font-bold text-orange-500">
                                                {formatCurrency(product.price)}
                                            </p>
                                            <p className="mb-3 text-xs font-semibold text-slate-400">
                                                Stock:{' '}
                                                <span className={product.stock === 0 ? 'text-red-500' : 'text-slate-600'}>
                                                    {product.stock} units
                                                </span>
                                            </p>
                                            {product.description ? (
                                                <p
                                                    title={product.description}
                                                    className="mb-4 line-clamp-2 cursor-help text-sm font-medium text-slate-500"
                                                >
                                                    {product.description}
                                                </p>
                                            ) : null}
                                            <div className="mt-auto border-t-2 border-dashed border-slate-100 pt-4">
                                                {showQRFor === product.id ? (
                                                    <div className="flex flex-col items-center justify-center rounded-xl border-2 border-black bg-slate-50 p-4 animate-in fade-in zoom-in duration-200">
                                                        <img
                                                            src={`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(`${window.location.origin}/buy/${product.id}`)}`}
                                                            alt={`QR Code ${product.name}`}
                                                            className="mb-2 h-24 w-24"
                                                        />
                                                        <p className="mb-3 text-center text-xs font-bold text-slate-500">
                                                            Scan to buy
                                                        </p>
                                                        <div className="flex w-full gap-2">
                                                            <button
                                                                type="button"
                                                                onClick={() => handlePrintQR(product)}
                                                                className="flex flex-1 items-center justify-center gap-1 rounded-lg bg-black py-2 text-xs font-bold text-white transition-colors hover:bg-orange-500"
                                                            >
                                                                <Printer className="h-3.5 w-3.5" /> Print
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => window.open(`/buy/${product.id}`, '_blank')}
                                                                className="flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-300 bg-white py-2 text-xs font-bold text-black transition-colors hover:bg-slate-100"
                                                            >
                                                                <ExternalLink className="h-3.5 w-3.5" /> Preview
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => setShowQRFor(null)}
                                                                className="rounded-lg bg-slate-200 p-2 font-bold text-black transition-colors hover:bg-slate-300"
                                                            >
                                                                <X className="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowQRFor(product.id)}
                                                        className="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-100 py-3 font-bold text-black transition-colors hover:bg-black hover:text-white"
                                                    >
                                                        <QrCode className="h-5 w-5" /> Show QR
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </main>
                </div>
            </div>

            {/* Order detail modal */}
            {orderModal && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-in fade-in duration-150"
                    onClick={(e) => e.target === e.currentTarget && setOrderModal(null)}
                >
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl animate-in zoom-in-95 duration-150">
                        {/* Header */}
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wider text-amber-500">Order Code</p>
                                <p className="font-mono text-3xl font-black tracking-widest text-slate-900">
                                    {orderModal.code}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setOrderModal(null)}
                                className="mt-1 rounded-full bg-slate-100 p-2 transition-colors hover:bg-slate-200"
                            >
                                <X className="h-4 w-4 text-slate-500" />
                            </button>
                        </div>

                        {/* Items */}
                        <div className="mb-5 rounded-xl bg-slate-50 p-4">
                            <div className="space-y-2">
                                {orderModal.items.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between gap-2 text-sm">
                                        <span className="text-slate-700">
                                            {item.name}{' '}
                                            <span className="text-slate-400">× {item.qty}</span>
                                        </span>
                                        <span className="font-bold text-slate-800">
                                            {formatCurrency(item.price * item.qty)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-xl font-black text-orange-500">
                                    {formatCurrency(orderModal.total)}
                                </span>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={() => {
                                    handleConfirmOrder(orderModal.id, orderModal);
                                    setOrderModal(null);
                                }}
                                disabled={confirmingId === orderModal.id || cancellingId === orderModal.id}
                                className="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-green-500 py-3 text-sm font-bold text-white shadow-[0_3px_0_0_#16a34a] transition-all active:translate-y-0.5 active:shadow-none hover:bg-green-600 disabled:opacity-60"
                            >
                                {confirmingId === orderModal.id ? (
                                    <><Loader2 className="h-4 w-4 animate-spin" /> Confirming...</>
                                ) : (
                                    <><Receipt className="h-4 w-4" /> Confirm Order</>
                                )}
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    handleCancelOrder(orderModal.id);
                                    setOrderModal(null);
                                }}
                                disabled={confirmingId === orderModal.id || cancellingId === orderModal.id}
                                className="flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-500 transition-colors hover:bg-red-100 disabled:opacity-60"
                            >
                                {cancellingId === orderModal.id ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    <XCircle className="h-4 w-4" />
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Order confirmed success modal */}
            {confirmedOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-in fade-in duration-150">
                    <div className="flex w-full max-w-sm flex-col items-center rounded-2xl bg-white p-8 shadow-2xl animate-in zoom-in-95 duration-150">
                        <div className="mb-5 flex h-20 w-20 rotate-3 items-center justify-center rounded-[1.5rem] bg-green-500 shadow-[0_6px_0_0_#16a34a]">
                            <CheckCircle2 className="h-10 w-10 -rotate-3 text-white" strokeWidth={2.5} />
                        </div>
                        <h2 className="mb-1 text-2xl font-black text-slate-900">Order Confirmed!</h2>
                        <p className="mb-5 text-sm text-slate-500">
                            Order{' '}
                            <span className="font-mono font-black text-slate-800">{confirmedOrder.code}</span>{' '}
                            has been approved.
                        </p>
                        <div className="mb-6 w-full rounded-xl bg-slate-50 p-4">
                            <div className="space-y-1.5">
                                {confirmedOrder.items.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between gap-2 text-sm">
                                        <span className="text-slate-600">
                                            {item.name} <span className="text-slate-400">× {item.qty}</span>
                                        </span>
                                        <span className="font-bold text-slate-800">
                                            {formatCurrency(item.price * item.qty)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-xl font-black text-green-600">
                                    {formatCurrency(confirmedOrder.total)}
                                </span>
                            </div>
                        </div>
                        <p className="text-sm font-semibold text-slate-400">
                            Returning in <span className="font-black text-slate-700">{countdown}</span>...
                        </p>
                        <button
                            type="button"
                            onClick={() => {
                                clearInterval(countdownRef.current);
                                setConfirmedOrder(null);
                                setCountdown(3);
                            }}
                            className="mt-3 text-xs font-bold text-slate-400 underline hover:text-slate-600"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
