import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    ChevronDown,
    Download,
    Edit2,
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
    Search,
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
    Zap,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

import { generateProductAssets } from '@/lib/qpay-ai';

function formatCurrency(value) {
    return `Rp ${Number(value).toLocaleString('id-ID')}`;
}

function formatCurrencyInput(raw) {
    const num = Number(String(raw).replace(/\D/g, ''));
    if (!num) return '';
    return num.toLocaleString('id-ID');
}

// ─── Simple Line/Dot Chart ────────────────────────────────────────────────────
function PerformanceChart({ data, hoveredDay, onHoverDot, onLeaveDot, onClickDot }) {
    const width = 900;
    const height = 260;
    const padL = 70;
    const padR = 20;
    const padT = 20;
    const padB = 40;

    const revenues = data.map((d) => d.revenue);
    const counts   = data.map((d) => d.count);
    const maxRev   = Math.max(...revenues, 1);
    const maxCnt   = Math.max(...counts, 1);

    const xStep = (width - padL - padR) / Math.max(data.length - 1, 1);

    const revPoints = data.map((d, i) => {
        const x = padL + i * xStep;
        const y = padT + (1 - d.revenue / maxRev) * (height - padT - padB);
        return `${x},${y}`;
    });
    const cntPoints = data.map((d, i) => {
        const x = padL + i * xStep;
        const y = padT + (1 - d.count / maxCnt) * (height - padT - padB);
        return `${x},${y}`;
    });

    const yTicks = 4;
    const labels = data.length > 15
        ? data.filter((_, i) => i % 4 === 0 || i === data.length - 1)
        : data;

    const hoveredIdx = hoveredDay ? data.findIndex((d) => d.dayNum === hoveredDay.dayNum) : -1;

    return (
        <svg
            viewBox={`0 0 ${width} ${height}`}
            className="w-full"
            style={{ height: 260 }}
        >
            {/* Grid lines */}
            {Array.from({ length: yTicks + 1 }).map((_, i) => {
                const y = padT + (i / yTicks) * (height - padT - padB);
                const revVal = Math.round(maxRev * (1 - i / yTicks));
                const label = revVal >= 1000
                    ? `Rp ${(revVal / 1000).toFixed(0)}k`
                    : `Rp ${revVal}`;
                const cntVal = Math.round(maxCnt * (1 - i / yTicks));
                return (
                    <g key={i}>
                        <line x1={padL} x2={width - padR} y1={y} y2={y} stroke="#e2e8f0" strokeDasharray="4 4" />
                        <text x={padL - 6} y={y + 4} textAnchor="end" fontSize={10} fill="#94a3b8">
                            {label}
                        </text>
                        <text x={width - padR + 6} y={y + 4} textAnchor="start" fontSize={10} fill="#94a3b8">
                            {cntVal}
                        </text>
                    </g>
                );
            })}

            {/* Hover guideline */}
            {hoveredIdx !== -1 && (
                <line
                    x1={padL + hoveredIdx * xStep}
                    x2={padL + hoveredIdx * xStep}
                    y1={padT}
                    y2={height - padB}
                    stroke="#cbd5e1"
                    strokeWidth={1.5}
                    strokeDasharray="4 4"
                    pointerEvents="none"
                />
            )}

            {/* Revenue line */}
            <polyline
                points={revPoints.join(' ')}
                fill="none"
                stroke="#f97316"
                strokeWidth={2.5}
                strokeLinejoin="round"
            />
            {/* Count line */}
            <polyline
                points={cntPoints.join(' ')}
                fill="none"
                stroke="#3b82f6"
                strokeWidth={2.5}
                strokeLinejoin="round"
            />

            {/* Dots revenue */}
            {data.map((d, i) => {
                const x = padL + i * xStep;
                const y = padT + (1 - d.revenue / maxRev) * (height - padT - padB);
                const hasData = d.count > 0 || d.revenue > 0;
                return (
                    <circle
                        key={`r${i}`}
                        cx={x}
                        cy={y}
                        r={4}
                        fill="#f97316"
                        stroke="#fff"
                        strokeWidth={2}
                        className={`transition-all duration-150 ${hasData ? 'cursor-pointer hover:r-[6px] hover:stroke-orange-600' : ''}`}
                        onMouseEnter={(e) => onHoverDot && onHoverDot(e, d)}
                        onMouseLeave={onLeaveDot}
                        onClick={() => onClickDot && onClickDot(d)}
                    />
                );
            })}
            {/* Dots count */}
            {data.map((d, i) => {
                const x = padL + i * xStep;
                const y = padT + (1 - d.count / maxCnt) * (height - padT - padB);
                const hasData = d.count > 0 || d.revenue > 0;
                return (
                    <circle
                        key={`c${i}`}
                        cx={x}
                        cy={y}
                        r={4}
                        fill="#3b82f6"
                        stroke="#fff"
                        strokeWidth={2}
                        className={`transition-all duration-150 ${hasData ? 'cursor-pointer hover:r-[6px] hover:stroke-blue-600' : ''}`}
                        onMouseEnter={(e) => onHoverDot && onHoverDot(e, d)}
                        onMouseLeave={onLeaveDot}
                        onClick={() => onClickDot && onClickDot(d)}
                    />
                );
            })}

            {/* X axis labels */}
            {labels.map((d, idx) => {
                const origIdx = data.indexOf(d);
                const x = padL + origIdx * xStep;
                return (
                    <text key={idx} x={x} y={height - 6} textAnchor="middle" fontSize={10} fill="#94a3b8">
                        {d.label}
                    </text>
                );
            })}
        </svg>
    );
}

// ─── Generate daily performance data for current month ───────────────────────
function buildChartData(confirmedOrders) {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const byDay = {};
    for (let d = 1; d <= daysInMonth; d++) {
        byDay[d] = { revenue: 0, count: 0, orders: [], dayNum: d };
    }

    (confirmedOrders || []).forEach((o) => {
        const date = new Date(o.created_at);
        if (date.getMonth() === month && date.getFullYear() === year) {
            const day = date.getDate();
            byDay[day].revenue += Number(o.total);
            byDay[day].count   += 1;
            byDay[day].orders.push(o);
        }
    });

    return Array.from({ length: daysInMonth }, (_, i) => {
        const day = i + 1;
        const label = `${month === 0 ? 'Jan' : ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][month]} ${String(day).padStart(2,'0')}`;
        return { label, ...byDay[day] };
    });
}

// ─── Edit Product Modal ───────────────────────────────────────────────────────
function EditProductModal({ product, onClose, onSave }) {
    const [nameVal,  setNameVal]  = useState(product.name);
    const [priceVal, setPriceVal] = useState(formatCurrencyInput(product.price));
    const [stockVal, setStockVal] = useState(String(product.stock));
    const [discVal,  setDiscVal]  = useState(formatCurrencyInput(product.discount ?? 0));
    const [descVal,  setDescVal]  = useState(product.description ?? '');
    const [imagePreview, setImagePreview] = useState(product.image ?? null);
    const [imageFile, setImageFile]       = useState(null);
    const [saving, setSaving] = useState(false);
    const fileRef = useRef(null);

    const handlePriceChange = (e) => {
        const raw = e.target.value.replace(/\D/g, '');
        setPriceVal(raw ? Number(raw).toLocaleString('id-ID') : '');
    };
    const handleDiscChange = (e) => {
        const raw = e.target.value.replace(/\D/g, '');
        setDiscVal(raw ? Number(raw).toLocaleString('id-ID') : '');
    };

    const handleImageChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        setImageFile(file);
        setImagePreview(URL.createObjectURL(file));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setSaving(true);

        const price    = Number(priceVal.replace(/\D/g, ''));
        const discount = Number(discVal.replace(/\D/g, '') || '0');
        const stock    = Number(stockVal);

        const data = {
            name: nameVal.trim(),
            price,
            discount,
            stock,
            description: descVal.trim() || null,
        };

        if (imageFile) {
            const fd = { ...data, image_file: imageFile };
            router.post(route('products.update', product.id), { ...fd, _method: 'PATCH' }, {
                forceFormData: true,
                onFinish: () => { setSaving(false); onSave(); },
            });
        } else {
            router.patch(route('products.update', product.id), data, {
                onFinish: () => { setSaving(false); onSave(); },
            });
        }
    };

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            onClick={(e) => e.target === e.currentTarget && onClose()}
        >
            <div className="w-full max-w-md rounded-2xl bg-white shadow-2xl animate-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100">
                    <h3 className="text-lg font-bold text-slate-900">Edit Product</h3>
                    <button type="button" onClick={onClose} className="rounded-full bg-slate-100 p-1.5 hover:bg-slate-200">
                        <X className="h-4 w-4 text-slate-500" />
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="overflow-y-auto px-6 py-4 space-y-4">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-semibold text-slate-600 mb-1">Product Name</label>
                        <input
                            required
                            value={nameVal}
                            onChange={(e) => setNameVal(e.target.value)}
                            className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-500 transition-colors"
                        />
                    </div>

                    {/* Price + Stock */}
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-sm font-semibold text-slate-600 mb-1">Price (Rp)</label>
                            <input
                                required
                                value={priceVal}
                                onChange={handlePriceChange}
                                inputMode="numeric"
                                className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-slate-600 mb-1">Stock</label>
                            <input
                                required
                                value={stockVal}
                                onChange={(e) => setStockVal(e.target.value.replace(/\D/g, ''))}
                                inputMode="numeric"
                                className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-500 transition-colors"
                            />
                        </div>
                    </div>

                    {/* Discount */}
                    <div>
                        <label className="block text-sm font-semibold text-slate-600 mb-1">Discount (Rp)</label>
                        <input
                            value={discVal}
                            onChange={handleDiscChange}
                            inputMode="numeric"
                            placeholder="0"
                            className="w-full rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-500 transition-colors"
                        />
                    </div>

                    {/* Image */}
                    <div>
                        <label className="block text-sm font-semibold text-slate-600 mb-1">Image</label>
                        <div
                            className="relative overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center cursor-pointer hover:border-orange-400 transition-colors"
                            style={{ minHeight: 140 }}
                            onClick={() => fileRef.current?.click()}
                        >
                            {imagePreview ? (
                                <img src={imagePreview} alt="Preview" className="h-36 w-full object-contain p-2" />
                            ) : (
                                <div className="flex flex-col items-center gap-1 py-8 text-slate-400">
                                    <UploadCloud className="h-8 w-8" />
                                    <span className="text-xs font-semibold">Click to upload</span>
                                </div>
                            )}
                            <input ref={fileRef} type="file" accept="image/jpeg,image/png,image/webp,image/gif" className="hidden" onChange={handleImageChange} />
                        </div>
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-semibold text-slate-600 mb-1">Description</label>
                        <textarea
                            value={descVal}
                            onChange={(e) => setDescVal(e.target.value)}
                            rows={3}
                            className="w-full resize-none rounded-xl border-2 border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-500 transition-colors"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={saving}
                        className="w-full flex items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-60 transition-colors"
                    >
                        {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Edit2 className="h-4 w-4" />}
                        {saving ? 'Saving...' : 'Update Product'}
                    </button>
                </form>
            </div>
        </div>
    );
}

// ─── Manual Sale Modal ────────────────────────────────────────────────────────
function ManualSaleModal({ product, onClose, onSuccess }) {
    const [qty, setQty]         = useState(1);
    const [unitPrice, setUnitPrice] = useState(formatCurrencyInput(product.price - (product.discount ?? 0)));
    const [recording, setRecording] = useState(false);
    const [done, setDone]           = useState(false);
    const [orderCode, setOrderCode] = useState('');

    const unitPriceNum = Number(unitPrice.replace(/\D/g, '') || '0');
    const grandTotal   = unitPriceNum * qty;

    const handleQtyChange = (delta) => setQty((q) => Math.max(1, q + delta));

    const handleUnitPriceChange = (e) => {
        const raw = e.target.value.replace(/\D/g, '');
        setUnitPrice(raw ? Number(raw).toLocaleString('id-ID') : '');
    };

    const handleRecord = async () => {
        setRecording(true);
        try {
            const res = await fetch(route('products.manual-sale', product.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ qty, unit_price: unitPriceNum }),
            });
            const json = await res.json();
            if (json.success) {
                setOrderCode(json.order_code);
                setDone(true);
                setTimeout(() => {
                    onSuccess();
                    onClose();
                }, 2000);
            }
        } catch {
            // ignore
        } finally {
            setRecording(false);
        }
    };

    if (done) {
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div className="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl text-center animate-in zoom-in-95 duration-150">
                    <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-green-500 shadow-[0_4px_0_0_#16a34a]">
                        <CheckCircle2 className="h-8 w-8 text-white" strokeWidth={2.5} />
                    </div>
                    <h3 className="text-xl font-black text-slate-900 mb-1">Sale Recorded!</h3>
                    <p className="text-sm text-slate-500">
                        Order <span className="font-mono font-black text-slate-800">{orderCode}</span>
                    </p>
                    <p className="mt-1 text-sm font-bold text-orange-500">{formatCurrency(grandTotal)}</p>
                </div>
            </div>
        );
    }

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            onClick={(e) => e.target === e.currentTarget && onClose()}
        >
            <div className="w-full max-w-sm rounded-2xl bg-white shadow-2xl animate-in zoom-in-95 duration-150">
                {/* Header */}
                <div className="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-100">
                    <div className="flex items-center gap-2">
                        <Zap className="h-5 w-5 text-orange-500" />
                        <h3 className="text-base font-bold text-slate-900">Manual Sale</h3>
                    </div>
                    <button type="button" onClick={onClose} className="rounded-full bg-slate-100 p-1.5 hover:bg-slate-200">
                        <X className="h-4 w-4 text-slate-500" />
                    </button>
                </div>

                <div className="px-6 py-4 space-y-4">
                    {/* Product info */}
                    <div className="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-100 bg-white">
                            {product.image ? (
                                <img src={product.image} alt={product.name} className="h-full w-full object-contain" />
                            ) : (
                                <ImageIcon className="h-6 w-6 text-slate-300" />
                            )}
                        </div>
                        <div>
                            <p className="font-bold text-slate-900 text-sm">{product.name}</p>
                            <p className="text-xs text-slate-500">Stock: {product.stock}</p>
                        </div>
                    </div>

                    {/* Adjustment Price */}
                    <div>
                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Adjustment Price (per item)
                        </label>
                        <div className="flex items-center gap-2 rounded-xl border-2 border-slate-200 px-3 py-2.5 focus-within:border-orange-500 transition-colors">
                            <span className="text-sm font-semibold text-slate-400">Rp</span>
                            <input
                                value={unitPrice}
                                onChange={handleUnitPriceChange}
                                inputMode="numeric"
                                className="flex-1 bg-transparent text-sm font-bold text-slate-800 outline-none"
                            />
                        </div>
                    </div>

                    {/* Quantity */}
                    <div>
                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Quantity
                        </label>
                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={() => handleQtyChange(-1)}
                                className="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-slate-200 text-lg font-bold text-slate-600 hover:border-orange-400 hover:text-orange-500 transition-colors"
                            >
                                -
                            </button>
                            <input
                                value={qty}
                                onChange={(e) => setQty(Math.max(1, Number(e.target.value.replace(/\D/g, '')) || 1))}
                                inputMode="numeric"
                                className="flex-1 rounded-xl border-2 border-slate-200 py-2 text-center text-base font-bold outline-none focus:border-orange-500 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => handleQtyChange(1)}
                                className="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-slate-200 text-lg font-bold text-slate-600 hover:border-orange-400 hover:text-orange-500 transition-colors"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    {/* Grand total */}
                    <div className="flex items-center justify-between pt-2 border-t border-dashed border-slate-200">
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-500">Grand Total</span>
                        <span className="text-xl font-black text-orange-500">{formatCurrency(grandTotal)}</span>
                    </div>

                    <button
                        type="button"
                        onClick={handleRecord}
                        disabled={recording || qty < 1 || unitPriceNum <= 0}
                        className="w-full flex items-center justify-center gap-2 rounded-xl bg-orange-500 py-3 text-sm font-bold text-white shadow-[0_3px_0_0_rgba(0,0,0,0.2)] hover:bg-orange-600 disabled:opacity-60 transition-colors active:translate-y-0.5 active:shadow-none"
                    >
                        {recording ? <Loader2 className="h-4 w-4 animate-spin" /> : <Zap className="h-4 w-4" />}
                        {recording ? 'Recording...' : 'Record Sale'}
                    </button>
                </div>
            </div>
        </div>
    );
}

// ─── Main Dashboard ───────────────────────────────────────────────────────────
export default function Dashboard() {
    const {
        auth,
        products,
        ai_enabled: serverAiEnabled,
        pending_orders: serverPendingOrders,
        monthly_revenue: monthlyRevenue = 0,
        monthly_orders: monthlyOrders   = 0,
        confirmed_orders: confirmedOrders = [],
    } = usePage().props;

    const user = auth.user;

    // Store name
    const [storeName, setStoreName] = useState(user.name);
    const [editingName, setEditingName] = useState(false);
    const storeNameRef = useRef(null);

    // Collapsible Sidebar Sections
    const [processOrderOpen, setProcessOrderOpen] = useState(false);
    const [addProductOpen, setAddProductOpen] = useState(false);

    const handleNameClick = () => { setEditingName(true); setTimeout(() => storeNameRef.current?.select(), 0); };
    const handleNameSave  = () => {
        const trimmed = storeName.trim();
        if (!trimmed) { setStoreName(user.name); setEditingName(false); return; }
        setEditingName(false);
        if (trimmed === user.name) return;
        router.patch(route('settings.store-name'), { name: trimmed }, { preserveScroll: true });
    };
    const handleNameKeyDown = (e) => {
        if (e.key === 'Enter')  { e.preventDefault(); storeNameRef.current?.blur(); }
        if (e.key === 'Escape') { setStoreName(user.name); setEditingName(false); }
    };

    // AI
    const [aiEnabled, setAiEnabled] = useState(serverAiEnabled ?? false);
    const [aiToggling, setAiToggling] = useState(false);
    const handleAiToggle = () => {
        const newValue = !aiEnabled;
        setAiEnabled(newValue);
        setAiToggling(true);
        router.patch(route('settings.ai-toggle'), { ai_enabled: newValue }, {
            preserveScroll: true,
            onFinish: () => setAiToggling(false),
        });
    };

    // Add product form
    const [isAddingProduct, setIsAddingProduct]   = useState(false);
    const [priceInput, setPriceInput]             = useState('');
    const [stockInput, setStockInput]             = useState('');
    const [discountInput, setDiscountInput]       = useState('');
    const [descriptionInput, setDescriptionInput] = useState('');
    const [imageFile, setImageFile]               = useState(null);
    const [imagePreview, setImagePreview]         = useState(null);
    const [regeneratingId, setRegeneratingId]     = useState(null);
    const fileInputRef = useRef(null);

    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const handleImageFileChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (!ALLOWED_IMAGE_TYPES.includes(file.type)) { alert('Only JPG, PNG, WEBP, or GIF images are allowed.'); e.target.value = ''; return; }
        if (file.size > 5 * 1024 * 1024) { alert('Image must be under 5 MB.'); e.target.value = ''; return; }
        setImageFile(file);
        setImagePreview(URL.createObjectURL(file));
    };
    const handleClearImage = () => {
        setImageFile(null);
        setImagePreview(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const handlePriceChange = (e) => {
        const raw = e.target.value.replace(/\D/g, '');
        setPriceInput(raw ? Number(raw).toLocaleString('id-ID') : '');
    };
    const handleDiscountChange = (e) => {
        const raw = e.target.value.replace(/\D/g, '');
        setDiscountInput(raw ? Number(raw).toLocaleString('id-ID') : '');
    };

    const handleAddProduct = async (event) => {
        event.preventDefault();
        const form  = event.target;
        const name  = form.productName.value.trim();
        const price = Number.parseInt(priceInput.replace(/\D/g, ''), 10);
        const stock = Number.parseInt(stockInput, 10);
        const discount = Number.parseInt(discountInput.replace(/\D/g, '') || '0', 10);
        if (!name || !price || Number.isNaN(stock)) return;

        setIsAddingProduct(true);
        let description = null;
        let image = null;

        if (imageFile) {
            router.post(route('products.store'), {
                name, price, stock, discount,
                description: descriptionInput.trim() || null,
                image_file: imageFile,
            }, {
                forceFormData: true,
                onFinish: () => {
                    form.reset(); setPriceInput(''); setStockInput(''); setDiscountInput('');
                    setDescriptionInput(''); setImageFile(null); setImagePreview(null); setIsAddingProduct(false);
                },
            });
            return;
        }

        if (aiEnabled) {
            const generated = await generateProductAssets(name);
            description = generated.description;
            image = generated.image;
        }
        if (descriptionInput.trim()) description = descriptionInput.trim();

        router.post(route('products.store'), { name, price, stock, discount, description, image }, {
            onFinish: () => {
                form.reset(); setPriceInput(''); setStockInput(''); setDiscountInput('');
                setDescriptionInput(''); setIsAddingProduct(false);
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
        }, { onFinish: () => setRegeneratingId(null) });
    };

    const handleDeleteProduct = (id) => router.delete(route('products.destroy', id));

    // Process Order (check code)
    const [orderCode, setOrderCode]   = useState('');
    const [checkingCode, setCheckingCode] = useState(false);
    const [searchError, setSearchError]   = useState('');
    const handleCheckCode = () => {
        const code = orderCode.trim().toUpperCase();
        if (!code) return;
        setCheckingCode(true);
        setSearchError('');
        fetch(route('orders.search', code))
            .then(async (res) => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || 'Failed to fetch order details.');
                }
                return data;
            })
            .then((data) => {
                setOrderModal(data);
                setOrderCode('');
            })
            .catch((err) => {
                setSearchError(err.message || 'Order code invalid or not found.');
            })
            .finally(() => {
                setCheckingCode(false);
            });
    };

    // QR
    const [showQRFor, setShowQRFor] = useState(null);
    const handlePrintQR = (product) => {
        const url   = `${window.location.origin}/buy/${product.id}`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        const win   = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html>
<html><head><title>QR — ${product.name}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,sans-serif;background:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh}
  .card{border:3px solid #000;border-radius:20px;padding:32px 28px;text-align:center;max-width:320px;width:100%}
  .brand{font-size:11px;font-weight:700;color:#f97316;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px}
  img{width:200px;height:200px}
  h2{margin:14px 0 4px;font-size:20px;font-weight:900}
  .price{font-size:22px;font-weight:800;color:#f97316;margin:4px 0 12px}
  .hint{font-size:12px;color:#64748b;font-weight:600}
</style></head><body>
<div class="card">
  <div class="brand">qpay</div>
  <img src="${qrUrl}" alt="QR Code"/>
  <h2>${product.name}</h2>
  <div class="price">Rp ${product.price.toLocaleString('id-ID')}</div>
  <div class="hint">📱 Scan to buy</div>
</div>
<script>window.onload=()=>window.print();<\/script>
</body></html>`);
        win.document.close();
    };

    // Pending orders
    const [pendingOrders, setPendingOrders]   = useState(serverPendingOrders ?? []);
    const [confirmingId, setConfirmingId]     = useState(null);
    const [cancellingId, setCancellingId]     = useState(null);
    const [orderModal, setOrderModal]         = useState(null);
    const [confirmedOrder, setConfirmedOrder] = useState(null);
    const [countdown, setCountdown]           = useState(3);
    const countdownRef = useRef(null);

    useEffect(() => {
        const timer = setInterval(() => {
            router.reload({ only: ['pending_orders'], preserveScroll: true, onSuccess: (page) => {
                setPendingOrders(page.props.pending_orders ?? []);
            }});
        }, 6000);
        return () => clearInterval(timer);
    }, []);
    useEffect(() => { setPendingOrders(serverPendingOrders ?? []); }, [serverPendingOrders]);

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
                            if (c <= 1) { clearInterval(countdownRef.current); setConfirmedOrder(null); return 3; }
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

    // Cart / buyer view
    const [view, setView]                     = useState('seller');
    const [cart, setCart]                     = useState([]);
    const [promoInput, setPromoInput]         = useState('');
    const [appliedPromo, setAppliedPromo]     = useState(null);
    const [promoMessage, setPromoMessage]     = useState('');
    const [checkoutState, setCheckoutState]   = useState('idle');

    const subtotal   = useMemo(() => cart.reduce((s, i) => s + i.price * i.qty, 0), [cart]);
    const discount   = useMemo(() => {
        if (!appliedPromo || subtotal <= 0) return 0;
        if (appliedPromo.type === 'percent') return subtotal * (appliedPromo.value / 100);
        return Math.min(appliedPromo.value, subtotal);
    }, [appliedPromo, subtotal]);
    const finalTotal = subtotal - discount;

    const handleDecrementQty = (id) => setCart((c) => {
        const item = c.find((e) => e.id === id);
        if (!item) return c;
        if (item.qty === 1) return c.filter((e) => e.id !== id);
        return c.map((e) => e.id === id ? { ...e, qty: e.qty - 1 } : e);
    });
    const handleIncrementQty = (id) => setCart((c) => c.map((e) => e.id === id ? { ...e, qty: e.qty + 1 } : e));
    const handleApplyPromo   = () => {
        const code = promoInput.toUpperCase().trim();
        if (!code) return;
        if (code === 'SAVE20') { setAppliedPromo({ code: 'SAVE20', type: 'percent', value: 20 }); setPromoMessage('✨ 20% discount applied!'); return; }
        if (code === 'OFF10K') { setAppliedPromo({ code: 'OFF10K', type: 'flat', value: 10000 }); setPromoMessage('✨ Rp 10,000 off applied!'); return; }
        setAppliedPromo(null); setPromoMessage('Coupon not found or expired.');
    };
    const handleRemovePromo = () => { setAppliedPromo(null); setPromoInput(''); setPromoMessage(''); };

    const handleCheckout = async () => {
        setCheckoutState('processing');
        try {
            await fetch(route('checkout'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ items: cart.map((i) => ({ id: i.id, qty: i.qty })) }),
            });
        } catch { /* best-effort */ }
        setTimeout(() => {
            setCheckoutState('success');
            setTimeout(() => {
                setCart([]); setAppliedPromo(null); setPromoInput(''); setPromoMessage('');
                setView('seller'); setCheckoutState('idle');
                router.reload({ only: ['products'] });
            }, 3500);
        }, 1500);
    };

    // Edit / Manual sale modals
    const [editProduct, setEditProduct]       = useState(null);
    const [manualSaleProduct, setManualSaleProduct] = useState(null);

    // Chart interaction states
    const [selectedDayDetails, setSelectedDayDetails] = useState(null);
    const [hoveredDay, setHoveredDay]                 = useState(null);
    const [chartTooltip, setChartTooltip]             = useState(null);
    const chartContainerRef                           = useRef(null);

    // Chart data
    const chartData = useMemo(() => buildChartData(confirmedOrders), [confirmedOrders]);

    // Aggregate products sold on the selected day
    const aggregatedProducts = useMemo(() => {
        if (!selectedDayDetails || !selectedDayDetails.orders) return [];
        const productMap = {};
        selectedDayDetails.orders.forEach((o) => {
            (o.items || []).forEach((item) => {
                const name = item.name;
                const qty = Number(item.qty || 0);
                const price = Number(item.price || 0);
                if (!productMap[name]) {
                    productMap[name] = { name, qty: 0, total: 0, price };
                }
                productMap[name].qty += qty;
                productMap[name].total += qty * price;
            });
        });
        return Object.values(productMap).sort((a, b) => b.total - a.total);
    }, [selectedDayDetails]);

    // ── Buyer view ──────────────────────────────────────────────────────────
    if (view === 'buyer') {
        return (
            <>
                <Head title="qpay Checkout" />
                <div className="flex min-h-screen flex-col items-center justify-center bg-slate-900 p-4 selection:bg-orange-500 selection:text-white">
                    <div className="relative flex h-[700px] w-full max-w-sm flex-col overflow-hidden rounded-[2.5rem] border-[6px] border-black bg-white shadow-2xl">
                        {checkoutState === 'success' ? (
                            <div className="absolute inset-0 z-50 flex flex-col items-center justify-center bg-black p-6 animate-in fade-in duration-300">
                                <div className="flex w-full flex-col items-center">
                                    <div className="mb-8 flex h-24 w-24 rotate-3 items-center justify-center rounded-[2rem] bg-orange-500 shadow-[0_8px_0_0_#c2410c]">
                                        <CheckCircle2 className="h-12 w-12 -rotate-3 text-black" strokeWidth={2.5} />
                                    </div>
                                    <h2 className="mb-3 text-4xl font-black tracking-tight text-white">Payment Complete!</h2>
                                    <p className="mb-10 text-center text-lg font-medium text-slate-400">{formatCurrency(finalTotal)} paid.</p>
                                    <div className="w-full max-w-[280px] rounded-[2rem] border-2 border-slate-800 bg-slate-900 p-6">
                                        <div className="mb-4 flex items-center justify-between border-b-2 border-dashed border-slate-800 pb-4">
                                            <span className="text-sm font-bold text-slate-500">Merchant</span>
                                            <span className="text-sm font-bold text-white">{user.name}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-bold text-slate-500">Status</span>
                                            <span className="flex items-center gap-1.5 text-sm font-black text-orange-500"><CheckCircle2 className="h-4 w-4" /> Paid</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : null}

                        <div className="relative shrink-0 bg-orange-500 p-6 text-center text-white">
                            <button type="button" onClick={() => setView('seller')} disabled={checkoutState !== 'idle'} className="absolute left-4 top-6 text-white/80 hover:text-white disabled:opacity-50">
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
                            ) : cart.map((item) => (
                                <div key={item.id} className="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                                    <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                        {item.image ? <img src={item.image} alt={item.name} className="h-full w-full object-cover mix-blend-multiply" /> : <ImageIcon className="h-6 w-6 text-slate-300" />}
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="line-clamp-1 text-sm font-bold text-black">{item.name}</h4>
                                        <p className="mt-0.5 text-sm font-bold text-orange-500">{formatCurrency(item.price)}</p>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                        <button type="button" onClick={() => handleDecrementQty(item.id)} className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-lg text-slate-600 shadow-sm hover:bg-red-50 hover:text-red-500">-</button>
                                        <span className="w-4 text-center text-sm font-bold">{item.qty}</span>
                                        <button type="button" onClick={() => handleIncrementQty(item.id)} className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-lg text-slate-600 shadow-sm hover:bg-green-50 hover:text-green-500">+</button>
                                    </div>
                                </div>
                            ))}
                            {cart.length > 0 ? (
                                <div className="mt-4 flex cursor-pointer flex-col items-center p-4 opacity-50 hover:opacity-100" onClick={() => setView('seller')}>
                                    <div className="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-200"><ArrowRight className="h-4 w-4 rotate-90 text-slate-500" /></div>
                                    <p className="text-center text-xs font-bold text-slate-500">Go back to scan more products</p>
                                </div>
                            ) : null}
                        </div>

                        {cart.length > 0 ? (
                            <div className="shrink-0 border-t-2 border-slate-100 bg-white px-6 pb-2 pt-4">
                                <div className="mb-1 flex items-center gap-2"><Ticket className="h-5 w-5 text-slate-400" /><span className="text-sm font-bold text-slate-700">Have a Coupon?</span></div>
                                <div className="mt-2 flex gap-2">
                                    <input type="text" value={promoInput} onChange={(e) => setPromoInput(e.target.value)} disabled={appliedPromo !== null || checkoutState !== 'idle'} placeholder="Enter SAVE20..." className="flex-1 rounded-xl border-2 border-slate-200 px-4 py-3 text-sm font-bold uppercase text-slate-700 outline-none focus:border-orange-500 disabled:bg-slate-50" />
                                    {appliedPromo ? (
                                        <button type="button" onClick={handleRemovePromo} disabled={checkoutState !== 'idle'} className="rounded-xl bg-red-50 px-5 py-3 text-sm font-bold text-red-500 hover:bg-red-100">Remove</button>
                                    ) : (
                                        <button type="button" onClick={handleApplyPromo} disabled={!promoInput.trim() || checkoutState !== 'idle'} className="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">Apply</button>
                                    )}
                                </div>
                                {promoMessage ? <p className={`mt-2 text-xs font-bold ${appliedPromo ? 'text-green-500' : 'text-red-500'}`}>{promoMessage}</p> : null}
                            </div>
                        ) : null}

                        <div className="relative z-20 shrink-0 border-t border-dashed border-slate-200 bg-white p-6">
                            {appliedPromo ? <div className="mb-1 flex items-center justify-between"><span className="text-sm text-slate-500">Subtotal</span><span className="text-sm text-slate-500">{formatCurrency(subtotal)}</span></div> : null}
                            {appliedPromo ? <div className="mb-3 flex items-center justify-between"><span className="text-sm font-bold text-orange-500">Coupon ({appliedPromo.code})</span><span className="text-sm font-bold text-orange-500">- {formatCurrency(discount)}</span></div> : null}
                            <div className="mb-4 flex items-center justify-between"><span className="font-bold text-slate-500">Total</span><span className="text-2xl font-bold text-black">{formatCurrency(finalTotal)}</span></div>
                            <button type="button" onClick={handleCheckout} disabled={cart.length === 0 || checkoutState !== 'idle'} className="flex w-full items-center justify-center gap-2 rounded-xl bg-black py-4 text-lg font-bold text-white shadow-lg hover:bg-orange-500 disabled:opacity-50">
                                {checkoutState === 'processing' ? <><Loader2 className="h-5 w-5 animate-spin" /> Processing...</> : <><Receipt className="h-5 w-5" /> Pay Now</>}
                            </button>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    // ── Seller view ─────────────────────────────────────────────────────────
    return (
        <>
            <Head title="qpay Dashboard" />

            <div className="flex h-screen flex-col overflow-hidden bg-slate-100 selection:bg-orange-500 selection:text-white">
                {/* Top Navbar */}
                <nav className="z-40 shrink-0 bg-white border-b border-slate-200 px-6 py-3 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <Link href="/" className="flex items-center gap-2">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500 shadow-sm transition-transform hover:scale-105">
                                    <QrCode className="h-5 w-5 text-white" />
                                </div>
                                <span className="text-xl font-black tracking-tight text-slate-900">
                                    q<span className="text-orange-500">pay</span>
                                </span>
                            </Link>

                            <div className="h-5 w-[1px] bg-slate-200" />

                            <div className="flex items-center gap-2">
                                {editingName ? (
                                    <input
                                        ref={storeNameRef}
                                        value={storeName}
                                        onChange={(e) => setStoreName(e.target.value)}
                                        onBlur={handleNameSave}
                                        onKeyDown={handleNameKeyDown}
                                        maxLength={255}
                                        className="bg-slate-100 text-sm font-bold text-slate-800 outline-none rounded-lg px-2 py-0.5 border border-orange-500 focus:ring-1 focus:ring-orange-500 w-40 transition-all"
                                    />
                                ) : (
                                    <button
                                        onClick={handleNameClick}
                                        className="group flex items-center gap-1.5 text-sm font-bold text-slate-700 hover:text-orange-500 transition-colors"
                                        title="Click to rename your store"
                                    >
                                        <span>{storeName}</span>
                                        <Edit2 className="h-3.5 w-3.5 opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 group-hover:text-orange-500" />
                                    </button>
                                )}
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link href={route('logout')} method="post" as="button" className="rounded-xl bg-slate-100 p-2.5 transition-colors hover:bg-red-50 hover:text-red-500 border border-transparent hover:border-red-200" title="Sign Out">
                                <LogOut className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </nav>

                {/* Body */}
                <div className="flex flex-1 overflow-hidden">
                    {/* ── Sidebar ── */}
                    <aside className="flex w-64 shrink-0 flex-col overflow-y-auto border-r border-slate-200 bg-white xl:w-72">

                        {/* Process Order */}
                        <div className="border-b border-slate-100">
                            <button
                                type="button"
                                onClick={() => setProcessOrderOpen(!processOrderOpen)}
                                className="flex w-full items-center justify-between p-5 text-left hover:bg-slate-50 transition-colors"
                            >
                                <h3 className="flex items-center gap-2 text-sm font-bold text-slate-800">
                                    <div className="flex h-6 w-6 items-center justify-center rounded-md bg-orange-500">
                                        <Search className="h-3.5 w-3.5 text-white" />
                                    </div>
                                    Process Order
                                </h3>
                                <ChevronDown className={`h-4 w-4 text-slate-400 transition-transform duration-200 ${processOrderOpen ? 'rotate-180' : ''}`} />
                            </button>

                            {processOrderOpen && (
                                <div className="px-5 pb-5 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div className="relative">
                                        <input
                                            type="text"
                                            value={orderCode}
                                            onChange={(e) => {
                                                setOrderCode(e.target.value.toUpperCase());
                                                if (searchError) setSearchError('');
                                            }}
                                            onKeyDown={(e) => e.key === 'Enter' && handleCheckCode()}
                                            placeholder="6-DIGIT CODE"
                                            maxLength={6}
                                            className={`w-full rounded-xl border-2 px-3 py-2.5 text-sm font-mono font-bold tracking-widest text-center outline-none transition-all mb-2 ${
                                                searchError 
                                                    ? 'border-red-300 focus:border-red-500 focus:ring-1 focus:ring-red-500' 
                                                    : 'border-slate-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 shadow-sm'
                                            }`}
                                        />
                                    </div>
                                    {searchError && (
                                        <p className="mb-2.5 text-xs font-semibold text-red-500 animate-in fade-in slide-in-from-top-1 duration-150">
                                            ⚠️ {searchError}
                                        </p>
                                    )}
                                    <button
                                        type="button"
                                        onClick={handleCheckCode}
                                        disabled={checkingCode || !orderCode.trim()}
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-2.5 text-sm font-bold text-white hover:bg-orange-600 disabled:opacity-60 shadow-sm hover:shadow transition-all active:translate-y-0.5"
                                    >
                                        {checkingCode ? (
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                        ) : (
                                            <Search className="h-4 w-4" />
                                        )}
                                        {checkingCode ? 'Checking...' : 'Check Code'}
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* Add Product */}
                        <div className="border-b border-slate-100">
                            <button
                                type="button"
                                onClick={() => setAddProductOpen(!addProductOpen)}
                                className="flex w-full items-center justify-between p-5 text-left hover:bg-slate-50 transition-colors"
                            >
                                <h3 className="flex items-center gap-2 text-sm font-bold text-slate-800">
                                    <div className="flex h-6 w-6 items-center justify-center rounded-md bg-slate-900">
                                        <Plus className="h-3.5 w-3.5 text-white" />
                                    </div>
                                    Add Product
                                </h3>
                                <ChevronDown className={`h-4 w-4 text-slate-400 transition-transform duration-200 ${addProductOpen ? 'rotate-180' : ''}`} />
                            </button>

                            {addProductOpen && (
                                <div className="px-5 pb-5 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <form onSubmit={handleAddProduct} className="space-y-2.5">
                                        <div>
                                            <label className="mb-1 block text-xs font-semibold text-slate-500">Product Name <span className="text-red-500">*</span></label>
                                            <input required name="productName" disabled={isAddingProduct} type="text" placeholder="Your Product Name" className="w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm outline-none focus:border-orange-500 disabled:opacity-50 transition-all shadow-sm" />
                                        </div>
                                        <div className="grid grid-cols-2 gap-2">
                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-500">Price <span className="text-red-500">*</span></label>
                                                <div className="relative flex items-center">
                                                    <span className="absolute left-3 text-xs font-bold text-slate-400 select-none">Rp</span>
                                                    <input
                                                        required
                                                        name="productPrice"
                                                        value={priceInput}
                                                        onChange={handlePriceChange}
                                                        disabled={isAddingProduct}
                                                        type="text"
                                                        placeholder="100.000"
                                                        className="w-full rounded-xl border-2 border-slate-200 pl-9 pr-3 py-2 text-sm outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 disabled:opacity-50 transition-all shadow-sm"
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-500">Stock <span className="text-red-500">*</span></label>
                                                <input
                                                    required
                                                    name="productStock"
                                                    value={stockInput}
                                                    onChange={(e) => setStockInput(e.target.value.replace(/\D/g, ''))}
                                                    disabled={isAddingProduct}
                                                    type="text"
                                                    inputMode="numeric"
                                                    placeholder="10"
                                                    className="w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 disabled:opacity-50 transition-all shadow-sm"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-xs font-semibold text-slate-500">Discount</label>
                                            <div className="relative flex items-center">
                                                <span className="absolute left-3 text-xs font-bold text-slate-400 select-none">Rp</span>
                                                <input
                                                    name="productDiscount"
                                                    value={discountInput}
                                                    onChange={handleDiscountChange}
                                                    disabled={isAddingProduct}
                                                    type="text"
                                                    inputMode="numeric"
                                                    placeholder="0"
                                                    className="w-full rounded-xl border-2 border-slate-200 pl-9 pr-3 py-2 text-sm outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 disabled:opacity-50 transition-all shadow-sm"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-xs font-semibold text-slate-500">
                                                Description {aiEnabled ? <span className="text-orange-500 font-bold">(AI Auto-generate)</span> : '(Optional)'}
                                            </label>
                                            <textarea
                                                name="productDescription"
                                                value={aiEnabled ? '' : descriptionInput}
                                                onChange={(e) => setDescriptionInput(e.target.value)}
                                                disabled={aiEnabled || isAddingProduct}
                                                placeholder={aiEnabled ? "AI will auto-generate this description..." : "Write a short description..."}
                                                rows={2}
                                                className={`w-full rounded-xl border-2 px-3 py-2 text-sm outline-none transition-all resize-none shadow-sm ${
                                                    aiEnabled 
                                                        ? 'bg-slate-50 border-dashed border-slate-200 text-slate-400' 
                                                        : 'border-slate-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500'
                                                }`}
                                            />
                                        </div>

                                        {/* AI Toggle inline */}
                                        <label className="flex items-center gap-2 cursor-pointer select-none py-1">
                                            <div
                                                onClick={!aiToggling ? handleAiToggle : undefined}
                                                className={`relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full transition-colors ${aiEnabled ? 'bg-orange-500' : 'bg-slate-200'} ${aiToggling ? 'opacity-60 cursor-not-allowed' : ''}`}
                                            >
                                                <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${aiEnabled ? 'translate-x-4' : 'translate-x-0.5'}`} />
                                            </div>
                                            {aiToggling ? (
                                                <Loader2 className="h-4 w-4 animate-spin text-orange-500" />
                                            ) : (
                                                <Sparkles className={`h-4 w-4 ${aiEnabled ? 'text-orange-500' : 'text-slate-400'}`} />
                                            )}
                                            <span className="text-xs font-semibold text-slate-600">
                                                {aiToggling ? 'Updating AI...' : 'Enable AI'}
                                            </span>
                                        </label>
                                        {aiEnabled && !aiToggling && <p className="text-xs text-slate-400 -mt-1">Auto generate product description and image</p>}

                                        {/* Image upload */}
                                        {aiEnabled ? (
                                            <div className="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-orange-200 bg-orange-50/40 p-4 text-center transition-all select-none">
                                                <Sparkles className="h-5 w-5 text-orange-500 animate-pulse" />
                                                <span className="text-xs font-bold text-orange-700">AI Image Active</span>
                                                <span className="text-[10px] text-slate-400 leading-tight">An image will be automatically generated for this product using AI.</span>
                                            </div>
                                        ) : imagePreview ? (
                                            <div className="relative overflow-hidden rounded-xl border-2 border-slate-200 bg-white">
                                                <img src={imagePreview} alt="Preview" className="h-28 w-full object-contain p-2" />
                                                <button type="button" onClick={handleClearImage} className="absolute right-2 top-2 rounded-full bg-black/60 p-1 text-white hover:bg-red-500"><X className="h-3 w-3" /></button>
                                            </div>
                                        ) : (
                                            <label className="flex cursor-pointer flex-col items-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 py-3 hover:border-orange-400 hover:bg-orange-50 transition-colors">
                                                <UploadCloud className="h-5 w-5 text-slate-400" />
                                                <span className="text-xs font-semibold text-slate-400">Upload image</span>
                                                <input ref={fileInputRef} type="file" accept="image/jpeg,image/png,image/webp,image/gif" className="hidden" disabled={isAddingProduct} onChange={handleImageFileChange} />
                                            </label>
                                        )}

                                        <button type="submit" disabled={isAddingProduct} className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-60 transition-colors">
                                            {isAddingProduct ? <><Loader2 className="h-4 w-4 animate-spin" /> Adding...</> : 'Create Product'}
                                        </button>
                                    </form>
                                </div>
                            )}
                        </div>

                        {/* Logout */}
                        <div className="mt-auto p-5">
                            <Link href={route('logout')} method="post" as="button" className="flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-red-500 transition-colors">
                                <LogOut className="h-4 w-4" /> Logout
                            </Link>
                        </div>
                    </aside>

                    {/* ── Main content ── */}
                    <main className="flex-1 overflow-y-auto">
                        {/* ── Monthly Performance Chart ── */}
                        <div className="m-6 mb-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="mb-4 flex items-start justify-between gap-4">
                                <div>
                                    <div className="flex items-center gap-2 mb-3">
                                        <TrendingUp className="h-5 w-5 text-orange-500" />
                                        <h2 className="text-base font-bold text-slate-900">Monthly Performance</h2>
                                    </div>
                                    <div className="flex items-center gap-8">
                                        <div>
                                            <p className="text-xs font-semibold text-slate-400 mb-0.5">Total Revenue</p>
                                            <p className="text-2xl font-black text-slate-900">{formatCurrency(monthlyRevenue)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs font-semibold text-slate-400 mb-0.5">Total Sold</p>
                                            <p className="text-2xl font-black text-slate-900">{monthlyOrders} <span className="text-base font-semibold text-slate-400">items</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center gap-4">
                                    <div className="flex items-center gap-1.5 text-xs font-semibold text-blue-500">
                                        <div className="h-2.5 w-2.5 rounded-full bg-blue-500" /> Items Sold
                                    </div>
                                    <div className="flex items-center gap-1.5 text-xs font-semibold text-orange-500">
                                        <div className="h-2.5 w-2.5 rounded-full bg-orange-500" /> Revenue
                                    </div>
                                    <button className="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                        <Download className="h-3.5 w-3.5" /> Export CSV
                                    </button>
                                </div>
                            </div>
                            <div className="overflow-x-auto relative" ref={chartContainerRef}>
                                <PerformanceChart
                                    data={chartData}
                                    hoveredDay={hoveredDay}
                                    onHoverDot={(event, dayData) => {
                                        if (!chartContainerRef.current) return;
                                        setHoveredDay(dayData);
                                        const dotRect = event.currentTarget.getBoundingClientRect();
                                        const containerRect = chartContainerRef.current.getBoundingClientRect();
                                        
                                        const xCenter = (dotRect.left + dotRect.width / 2) - containerRect.left;
                                        const isRightSide = xCenter < containerRect.width * 0.75;
                                        const yCenter = (dotRect.top + dotRect.height / 2) - containerRect.top;
                                        
                                        const isNearTop = yCenter < 50;
                                        const isNearBottom = yCenter > containerRect.height - 50;
                                        
                                        let x, y, transform;
                                        if (isRightSide) {
                                            x = dotRect.right - containerRect.left + 8;
                                            if (isNearTop) {
                                                y = 8;
                                                transform = "none";
                                            } else if (isNearBottom) {
                                                y = containerRect.height - 8;
                                                transform = "translateY(-100%)";
                                            } else {
                                                y = yCenter;
                                                transform = "translateY(-50%)";
                                            }
                                        } else {
                                            x = dotRect.left - containerRect.left - 8;
                                            if (isNearTop) {
                                                y = 8;
                                                transform = "translateX(-100%)";
                                            } else if (isNearBottom) {
                                                y = containerRect.height - 8;
                                                transform = "translateX(-100%) translateY(-100%)";
                                            } else {
                                                y = yCenter;
                                                transform = "translateX(-100%) translateY(-50%)";
                                            }
                                        }
                                        
                                        setChartTooltip({
                                            x,
                                            y,
                                            label: dayData.label,
                                            revenue: dayData.revenue,
                                            count: dayData.count,
                                            transform,
                                        });
                                    }}
                                    onLeaveDot={() => {
                                        setHoveredDay(null);
                                        setChartTooltip(null);
                                    }}
                                    onClickDot={(dayData) => {
                                        if (dayData.count > 0 || dayData.revenue > 0) {
                                            setSelectedDayDetails(dayData);
                                        }
                                    }}
                                />
                                {chartTooltip && (
                                    <div
                                        className="absolute pointer-events-none z-10 bg-slate-900/95 text-white px-3 py-2.5 rounded-xl shadow-xl text-xs flex flex-col gap-1 transition-all duration-75 border border-slate-700/50 backdrop-blur-sm min-w-[140px]"
                                        style={{ left: chartTooltip.x, top: chartTooltip.y, transform: chartTooltip.transform }}
                                    >
                                        <p className="font-bold text-slate-400 border-b border-slate-700/50 pb-1 mb-1">{chartTooltip.label}</p>
                                        <div className="flex items-center gap-1.5 font-semibold text-slate-300">
                                            <div className="h-2 w-2 rounded-full bg-blue-500" />
                                            <span>Orders: <span className="font-black text-white">{chartTooltip.count} items</span></span>
                                        </div>
                                        <div className="flex items-center gap-1.5 font-semibold text-slate-300">
                                            <div className="h-2 w-2 rounded-full bg-orange-500" />
                                            <span>Revenue: <span className="font-black text-white">{formatCurrency(chartTooltip.revenue)}</span></span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Pending Orders */}
                        {pendingOrders.length > 0 && (
                            <div className="mx-6 mb-4">
                                <div className="mb-3 flex items-center gap-2">
                                    <div className="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500">
                                        <Timer className="h-3.5 w-3.5 text-white" />
                                    </div>
                                    <h2 className="text-base font-bold text-black">Pending Orders</h2>
                                    <span className="animate-pulse rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">{pendingOrders.length} waiting</span>
                                </div>
                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                                    {pendingOrders.map((order) => (
                                        <button key={order.id} type="button" onClick={() => setOrderModal(order)} className="group flex flex-col gap-2 rounded-2xl border-2 border-amber-200 bg-white p-4 text-left shadow-sm hover:border-amber-400 hover:shadow-md active:scale-[0.98] transition-all">
                                            <div className="flex items-center justify-between">
                                                <span className="font-mono text-xl font-black tracking-widest text-slate-900">{order.code}</span>
                                                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100"><Timer className="h-3.5 w-3.5 text-amber-600" /></span>
                                            </div>
                                            <p className="text-base font-black text-orange-500">{formatCurrency(order.total)}</p>
                                            <p className="line-clamp-1 text-xs text-slate-400">{order.items.map((i) => `${i.name} ×${i.qty}`).join(', ')}</p>
                                            <p className="text-xs font-semibold text-amber-600 group-hover:text-amber-700">Tap to review →</p>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Product Catalog */}
                        <div className="mx-6 mb-6">
                            <div className="mb-4 flex items-center gap-3">
                                <Package className="h-6 w-6 text-orange-500" />
                                <div>
                                    <h2 className="text-xl font-bold text-slate-900">Product Catalog</h2>
                                    <p className="text-sm text-slate-400">Manage your store items and generate payment QR codes.</p>
                                </div>
                            </div>

                            {products.length === 0 ? (
                                <div className="flex h-64 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white">
                                    <Package className="mb-3 h-10 w-10 text-slate-300" />
                                    <p className="font-bold text-slate-400">No products yet.</p>
                                    <p className="text-sm text-slate-400">Add your first product using the sidebar.</p>
                                </div>
                            ) : (
                                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                                    {products.map((product) => {
                                        const effectivePrice = product.price - (product.discount ?? 0);
                                        return (
                                            <div key={product.id} className="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                                                {/* Image */}
                                                <div className="relative flex h-44 items-center justify-center overflow-hidden border-b border-slate-100 bg-white">
                                                    {regeneratingId === product.id ? (
                                                        <div className="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
                                                            <Loader2 className="mb-2 h-8 w-8 animate-spin text-orange-500" />
                                                            <span className="text-sm font-bold text-slate-800">Regenerating...</span>
                                                        </div>
                                                    ) : null}
                                                    {product.image ? (
                                                        <img src={product.image} alt={product.name} className="h-full w-full object-contain p-2" />
                                                    ) : (
                                                        <div className="flex flex-col items-center gap-2 text-slate-200">
                                                            <ImageIcon className="h-12 w-12" />
                                                            <span className="text-xs font-semibold text-slate-300">No image</span>
                                                        </div>
                                                    )}
                                                    {/* Hover actions */}
                                                    <div className="absolute right-3 top-3 z-10 flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                                        <button type="button" onClick={() => setEditProduct(product)} className="rounded-xl border border-slate-100 bg-white p-2 text-slate-400 shadow-md hover:text-blue-500 transition-colors" title="Edit Product">
                                                            <Edit2 className="h-4 w-4" />
                                                        </button>
                                                        {aiEnabled ? (
                                                            <button type="button" onClick={() => handleRegenerateProduct(product)} disabled={regeneratingId === product.id} className="rounded-xl border border-slate-100 bg-white p-2 text-slate-400 shadow-md hover:text-purple-500 transition-colors" title="Regenerate with AI">
                                                                <RefreshCw className="h-4 w-4" />
                                                            </button>
                                                        ) : null}
                                                        <button type="button" onClick={() => handleDeleteProduct(product.id)} disabled={regeneratingId === product.id} className="rounded-xl border border-slate-100 bg-white p-2 text-slate-400 shadow-md hover:text-red-500 transition-colors" title="Delete Product">
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </div>

                                                {/* Info */}
                                                <div className="flex flex-1 flex-col p-4">
                                                    <h4 className="mb-1 text-base font-bold text-slate-900">{product.name}</h4>
                                                    <div className="mb-1 flex items-center gap-2">
                                                        <p className="text-lg font-black text-orange-500">{formatCurrency(effectivePrice)}</p>
                                                        {product.discount > 0 && (
                                                            <p className="text-sm font-medium text-slate-400 line-through">{formatCurrency(product.price)}</p>
                                                        )}
                                                    </div>
                                                    <p className={`mb-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold w-fit ${product.stock === 0 ? 'bg-red-50 text-red-500' : product.stock <= 5 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600'}`}>
                                                        Stock: {product.stock}
                                                    </p>
                                                    {product.description ? (
                                                        <p title={product.description} className="mb-3 line-clamp-2 text-sm text-slate-500 italic">
                                                            "{product.description}"
                                                        </p>
                                                    ) : null}

                                                    {/* Actions */}
                                                    <div className="mt-auto pt-3 border-t border-dashed border-slate-100">
                                                        {showQRFor === product.id ? (
                                                            <div className="flex flex-col items-center rounded-xl border-2 border-black bg-slate-50 p-3">
                                                                <img src={`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(`${window.location.origin}/buy/${product.id}`)}`} alt={`QR ${product.name}`} className="mb-2 h-24 w-24" />
                                                                <p className="mb-2 text-xs font-bold text-slate-500">Scan to buy</p>
                                                                <div className="flex w-full gap-1.5">
                                                                    <button type="button" onClick={() => handlePrintQR(product)} className="flex flex-1 items-center justify-center gap-1 rounded-lg bg-black py-2 text-xs font-bold text-white hover:bg-orange-500 transition-colors"><Printer className="h-3.5 w-3.5" /> Print</button>
                                                                    <button type="button" onClick={() => window.open(`/buy/${product.id}`, '_blank')} className="flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-300 bg-white py-2 text-xs font-bold text-black hover:bg-slate-100 transition-colors"><ExternalLink className="h-3.5 w-3.5" /> Preview</button>
                                                                    <button type="button" onClick={() => setShowQRFor(null)} className="rounded-lg bg-slate-200 p-2 font-bold hover:bg-slate-300 transition-colors"><X className="h-4 w-4" /></button>
                                                                </div>
                                                            </div>
                                                        ) : (
                                                            <div className="flex gap-2">
                                                                <button type="button" onClick={() => setShowQRFor(product.id)} className="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-900 hover:text-white transition-colors">
                                                                    <QrCode className="h-4 w-4" /> QR
                                                                </button>
                                                                <button type="button" onClick={() => setManualSaleProduct(product)} className="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-orange-500 py-2.5 text-sm font-bold text-white hover:bg-orange-600 transition-colors shadow-[0_3px_0_0_rgba(0,0,0,0.15)]">
                                                                    <Zap className="h-4 w-4" /> Sale
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </main>
                </div>
            </div>

            {/* ── Daily Performance Details Modal ── */}
            {selectedDayDetails && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-in fade-in duration-200"
                    onClick={(e) => e.target === e.currentTarget && setSelectedDayDetails(null)}
                >
                    <div className="w-full max-w-4xl rounded-2xl bg-white p-6 shadow-2xl animate-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
                        {/* Header */}
                        <div className="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 className="text-xl font-black text-slate-900">Performance Details</h3>
                                <p className="text-sm font-semibold text-slate-400">{selectedDayDetails.label}</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSelectedDayDetails(null)}
                                className="rounded-full bg-slate-100 p-2 hover:bg-slate-200 transition-colors"
                            >
                                <X className="h-5 w-5 text-slate-500" />
                            </button>
                        </div>

                        {/* Summary Cards */}
                        <div className="grid grid-cols-3 gap-4 my-6">
                            <div className="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Total Revenue</p>
                                <p className="text-2xl font-black text-orange-500">{formatCurrency(selectedDayDetails.revenue)}</p>
                            </div>
                            <div className="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Orders Count</p>
                                <p className="text-2xl font-black text-blue-500">{selectedDayDetails.count} <span className="text-sm font-bold text-slate-400">transactions</span></p>
                            </div>
                            <div className="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Items Sold</p>
                                <p className="text-2xl font-black text-violet-500">
                                    {aggregatedProducts.reduce((sum, p) => sum + p.qty, 0)} <span className="text-sm font-bold text-slate-400">units</span>
                                </p>
                            </div>
                        </div>

                        {/* Two Columns Section */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto flex-1 min-h-0 pr-1">
                            {/* Product Sales Breakdown */}
                            <div className="flex flex-col">
                                <h4 className="text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
                                    <Package className="h-4 w-4 text-orange-500" /> Product Breakdown
                                </h4>
                                <div className="flex-1 rounded-2xl border border-slate-100 overflow-hidden bg-slate-50/30">
                                    <table className="w-full text-left border-collapse text-sm">
                                        <thead>
                                            <tr className="border-b border-slate-100 bg-slate-100/50 text-slate-500 font-bold">
                                                <th className="px-4 py-3">Product</th>
                                                <th className="px-4 py-3 text-center">Qty</th>
                                                <th className="px-4 py-3 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {aggregatedProducts.length === 0 ? (
                                                <tr>
                                                    <td colSpan={3} className="px-4 py-8 text-center text-slate-400 font-medium">No items sold on this day</td>
                                                </tr>
                                            ) : (
                                                aggregatedProducts.map((p, idx) => (
                                                    <tr key={idx} className="border-b border-slate-50 hover:bg-white transition-colors">
                                                        <td className="px-4 py-3 font-semibold text-slate-800">{p.name}</td>
                                                        <td className="px-4 py-3 text-center font-bold text-slate-600">{p.qty}</td>
                                                        <td className="px-4 py-3 text-right font-black text-slate-900">{formatCurrency(p.total)}</td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Transactions Log */}
                            <div className="flex flex-col">
                                <h4 className="text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
                                    <Receipt className="h-4 w-4 text-blue-500" /> Transactions Log
                                </h4>
                                <div className="space-y-3 overflow-y-auto flex-1 max-h-[350px] pr-1">
                                    {(!selectedDayDetails.orders || selectedDayDetails.orders.length === 0) ? (
                                        <div className="rounded-2xl border border-slate-100 p-6 text-center text-slate-400 bg-slate-50/30 font-medium">No transactions on this day</div>
                                    ) : (
                                        selectedDayDetails.orders.map((o, idx) => (
                                            <div
                                                key={idx}
                                                onClick={() => {
                                                    setOrderModal({
                                                        id: o.id,
                                                        code: o.code,
                                                        status: 'confirmed',
                                                        total: o.total,
                                                        items: o.items || []
                                                    });
                                                }}
                                                className="group cursor-pointer rounded-2xl border border-slate-100 p-4 bg-slate-50/30 hover:border-blue-400 hover:bg-white transition-all active:scale-[0.99]"
                                            >
                                                <div className="flex items-center justify-between mb-2">
                                                    <span className="font-mono text-lg font-black tracking-wider text-slate-900 group-hover:text-blue-500 transition-colors">
                                                        {o.code}
                                                    </span>
                                                    <span className="text-xs font-semibold text-slate-400">
                                                        {new Date(o.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                                                    </span>
                                                </div>
                                                <div className="flex items-end justify-between">
                                                    <p className="text-xs text-slate-500 line-clamp-1 max-w-[200px]">
                                                        {(o.items || []).map((i) => `${i.name} ×${i.qty}`).join(', ')}
                                                    </p>
                                                    <p className="text-sm font-black text-orange-500">{formatCurrency(o.total)}</p>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* ── Order detail modal ── */}
            {orderModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" onClick={(e) => e.target === e.currentTarget && setOrderModal(null)}>
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl animate-in zoom-in-95 duration-150">
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wider text-amber-500">Order Code</p>
                                <p className="font-mono text-3xl font-black tracking-widest text-slate-900">{orderModal.code}</p>
                            </div>
                            <button type="button" onClick={() => setOrderModal(null)} className="mt-1 rounded-full bg-slate-100 p-2 hover:bg-slate-200"><X className="h-4 w-4 text-slate-500" /></button>
                        </div>
                        <div className="mb-5 rounded-xl bg-slate-50 p-4">
                            <div className="space-y-2">
                                {orderModal.items.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between gap-2 text-sm">
                                        <span className="text-slate-700">{item.name} <span className="text-slate-400">× {item.qty}</span></span>
                                        <span className="font-bold text-slate-800">{formatCurrency(item.price * item.qty)}</span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-xl font-black text-orange-500">{formatCurrency(orderModal.total)}</span>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            {orderModal.status === 'pending' || !orderModal.status ? (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => { handleConfirmOrder(orderModal.id, orderModal); setOrderModal(null); }}
                                        disabled={confirmingId === orderModal.id || cancellingId === orderModal.id}
                                        className="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-green-500 py-3 text-sm font-bold text-white shadow-[0_3px_0_0_#16a34a] hover:bg-green-600 disabled:opacity-60 transition-all hover:shadow-none hover:translate-y-0.5"
                                    >
                                        {confirmingId === orderModal.id ? (
                                            <><Loader2 className="h-4 w-4 animate-spin" /> Confirming...</>
                                        ) : (
                                            <><CheckCircle2 className="h-4 w-4" /> Confirm Payment</>
                                        )}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => { handleCancelOrder(orderModal.id); setOrderModal(null); }}
                                        disabled={confirmingId === orderModal.id || cancellingId === orderModal.id}
                                        className="flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-100 disabled:opacity-60 transition-colors"
                                        title="Cancel Order"
                                    >
                                        {cancellingId === orderModal.id ? (
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                        ) : (
                                            <XCircle className="h-4 w-4" />
                                        )}
                                    </button>
                                </>
                            ) : (
                                <div className={`w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm border ${
                                    orderModal.status === 'confirmed'
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-red-50 text-red-700 border-red-200'
                                }`}>
                                    {orderModal.status === 'confirmed' ? (
                                        <>
                                            <CheckCircle2 className="h-4 w-4 text-green-600" />
                                            <span>Payment Confirmed / Paid</span>
                                        </>
                                    ) : (
                                        <>
                                            <XCircle className="h-4 w-4 text-red-600" />
                                            <span>Order Cancelled</span>
                                        </>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* ── Confirmed order success modal ── */}
            {confirmedOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-in fade-in duration-150">
                    <div className="flex w-full max-w-sm flex-col items-center rounded-2xl bg-white p-8 shadow-2xl animate-in zoom-in-95 duration-150">
                        <div className="mb-5 flex h-20 w-20 rotate-3 items-center justify-center rounded-[1.5rem] bg-green-500 shadow-[0_6px_0_0_#16a34a]">
                            <CheckCircle2 className="h-10 w-10 -rotate-3 text-white" strokeWidth={2.5} />
                        </div>
                        <h2 className="mb-1 text-2xl font-black text-slate-900">Order Confirmed!</h2>
                        <p className="mb-5 text-sm text-slate-500">Order <span className="font-mono font-black text-slate-800">{confirmedOrder.code}</span> approved.</p>
                        <div className="mb-6 w-full rounded-xl bg-slate-50 p-4">
                            <div className="space-y-1.5">
                                {confirmedOrder.items.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between gap-2 text-sm">
                                        <span className="text-slate-600">{item.name} <span className="text-slate-400">× {item.qty}</span></span>
                                        <span className="font-bold text-slate-800">{formatCurrency(item.price * item.qty)}</span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-xl font-black text-green-600">{formatCurrency(confirmedOrder.total)}</span>
                            </div>
                        </div>
                        <p className="text-sm font-semibold text-slate-400">Returning in <span className="font-black text-slate-700">{countdown}</span>...</p>
                        <button type="button" onClick={() => { clearInterval(countdownRef.current); setConfirmedOrder(null); setCountdown(3); }} className="mt-3 text-xs font-bold text-slate-400 underline hover:text-slate-600">Dismiss</button>
                    </div>
                </div>
            )}

            {/* ── Edit Product Modal ── */}
            {editProduct && (
                <EditProductModal
                    product={editProduct}
                    onClose={() => setEditProduct(null)}
                    onSave={() => { setEditProduct(null); router.reload({ only: ['products'] }); }}
                />
            )}

            {/* ── Manual Sale Modal ── */}
            {manualSaleProduct && (
                <ManualSaleModal
                    product={manualSaleProduct}
                    onClose={() => setManualSaleProduct(null)}
                    onSuccess={() => router.reload({ only: ['products', 'monthly_revenue', 'monthly_orders'] })}
                />
            )}
        </>
    );
}
