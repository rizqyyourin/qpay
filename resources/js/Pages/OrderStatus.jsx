import { Head, usePage } from '@inertiajs/react';
import { CheckCircle2, ChevronLeft, Clock, QrCode, XCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

function formatCurrency(value) {
    return `Rp ${value.toLocaleString('id-ID')}`;
}

export default function OrderStatus() {
    const { order: initialOrder } = usePage().props;

    const [status, setStatus] = useState(initialOrder.status);
    const [elapsed, setElapsed] = useState(
        Math.floor((Date.now() - new Date(initialOrder.created_at).getTime()) / 1000),
    );
    const [cancelLoading, setCancelLoading] = useState(false);
    const [approveLoading, setApproveLoading] = useState(false);
    const [actionError, setActionError] = useState(null);
    const timerRef = useRef(null);
    const elapsedRef = useRef(null);

    // Poll for status changes while pending
    useEffect(() => {
        if (status !== 'pending') return;

        timerRef.current = setInterval(async () => {
            try {
                const res = await fetch(`/api/order/${initialOrder.code}/status`);
                const data = await res.json();
                if (data.status !== 'pending') {
                    setStatus(data.status);
                    clearInterval(timerRef.current);
                }
            } catch {
                // network error — keep polling
            }
        }, 3000);

        return () => clearInterval(timerRef.current);
    }, [status]);

    // Track elapsed seconds for manual approve unlock
    useEffect(() => {
        if (status !== 'pending') return;

        elapsedRef.current = setInterval(() => {
            setElapsed(Math.floor((Date.now() - new Date(initialOrder.created_at).getTime()) / 1000));
        }, 1000);

        return () => clearInterval(elapsedRef.current);
    }, [status]);

    const handleCancel = async () => {
        if (!window.confirm('Are you sure you want to cancel this order?')) return;
        setCancelLoading(true);
        setActionError(null);
        try {
            const res = await fetch(`/api/order/${initialOrder.code}/cancel`, { method: 'POST' });
            const data = await res.json();
            if (res.ok) {
                setStatus(data.status);
            } else {
                setActionError(data.error ?? 'Failed to cancel order.');
            }
        } catch {
            setActionError('Network error. Please try again.');
        } finally {
            setCancelLoading(false);
        }
    };

    const handleManualApprove = async () => {
        if (!window.confirm('Confirm manual approval? Only do this if you have already paid in cash.')) return;
        setApproveLoading(true);
        setActionError(null);
        try {
            const res = await fetch(`/api/order/${initialOrder.code}/approve`, { method: 'POST' });
            const data = await res.json();
            if (res.ok) {
                setStatus(data.status);
            } else {
                setActionError(data.error ?? 'Failed to approve order.');
            }
        } catch {
            setActionError('Network error. Please try again.');
        } finally {
            setApproveLoading(false);
        }
    };

    const showManualApprove = elapsed >= 60;

    const total = initialOrder.total;
    const items = initialOrder.items;

    /* ── Confirmed ── */
    if (status === 'confirmed') {
        return (
            <>
                <Head title="Order Confirmed — qpay" />
                <div className="min-h-screen bg-slate-200">
                    <div className="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center bg-black p-6 shadow-xl selection:bg-orange-500 selection:text-white">
                        <div className="flex w-full flex-col items-center animate-in fade-in duration-300">
                            <div className="mb-6 flex h-20 w-20 rotate-3 items-center justify-center rounded-[2rem] bg-orange-500 shadow-[0_6px_0_0_#c2410c]">
                                <CheckCircle2 className="h-10 w-10 -rotate-3 text-black" strokeWidth={2.5} />
                            </div>
                            <h1 className="mb-1 text-3xl font-black text-white">Order Confirmed!</h1>
                            <p className="mb-6 text-center text-slate-400">
                                The seller has confirmed your order.
                            </p>

                            <div className="w-full rounded-2xl border-2 border-slate-800 bg-slate-900 p-5">
                                <div className="mb-3 flex items-center justify-between border-b border-dashed border-slate-800 pb-3">
                                    <span className="text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Order Code
                                    </span>
                                    <span className="font-mono text-lg font-black tracking-widest text-orange-400">
                                        {initialOrder.code}
                                    </span>
                                </div>
                                <div className="mb-3 space-y-2 border-b border-dashed border-slate-800 pb-3">
                                    {items.map((item, i) => (
                                        <div key={i} className="flex items-center justify-between gap-2">
                                            <span className="line-clamp-1 min-w-0 text-sm text-slate-300">
                                                {item.name}{' '}
                                                <span className="text-slate-500">× {item.qty}</span>
                                            </span>
                                            <span className="shrink-0 text-sm font-bold text-white">
                                                {formatCurrency(item.price * item.qty)}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="font-bold text-slate-400">Total</span>
                                    <span className="text-xl font-black text-orange-500">
                                        {formatCurrency(total)}
                                    </span>
                                </div>
                            </div>

                            <p className="mt-8 flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                                <QrCode className="h-3.5 w-3.5" /> Powered by qpay
                            </p>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    /* ── Cancelled ── */
    if (status === 'cancelled') {
        return (
            <>
                <Head title="Order Cancelled — qpay" />
                <div className="min-h-screen bg-slate-200">
                    <div className="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center bg-slate-950 p-6 shadow-xl">
                        <div className="flex w-full flex-col items-center animate-in fade-in duration-300">
                            <div className="mb-6 flex h-20 w-20 rotate-3 items-center justify-center rounded-[2rem] bg-red-500 shadow-[0_6px_0_0_#991b1b]">
                                <XCircle className="h-10 w-10 -rotate-3 text-white" strokeWidth={2.5} />
                            </div>
                            <h1 className="mb-2 text-3xl font-black text-white">Order Cancelled</h1>
                            <p className="mb-8 text-center text-slate-400">
                                This order was cancelled by the seller. Please contact the store for assistance.
                            </p>
                            <a
                                href={`/buy/${items[0]?.id ?? ''}`}
                                onClick={(e) => { e.preventDefault(); window.history.back(); }}
                                className="flex items-center gap-2 rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition-colors hover:border-slate-500 hover:text-white"
                            >
                                <ChevronLeft className="h-4 w-4" /> Go back
                            </a>
                            <p className="mt-8 flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                                <QrCode className="h-3.5 w-3.5" /> Powered by qpay
                            </p>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    /* ── Pending (default) ── */
    return (
        <>
            <Head title={`Order ${initialOrder.code} — Waiting`} />
            <div className="min-h-screen bg-slate-200">
                <div className="mx-auto flex min-h-screen max-w-md flex-col bg-white shadow-xl selection:bg-orange-500 selection:text-white">
                    {/* Header */}
                    <header className="shrink-0 bg-orange-500 px-4 py-3 text-center text-white shadow-md">
                        <p className="text-xs font-semibold text-orange-100">qpay Checkout</p>
                        <p className="text-sm font-bold">Waiting for Confirmation</p>
                    </header>

                    <div className="flex flex-1 flex-col items-center justify-center px-6 py-10">
                        {/* Animated waiting indicator */}
                        <div className="relative mb-8 flex h-28 w-28 items-center justify-center rounded-full bg-orange-50">
                            <div className="absolute inset-0 animate-ping rounded-full bg-orange-100 opacity-75" />
                            <Clock className="relative h-12 w-12 text-orange-500" />
                        </div>

                        <h1 className="mb-2 text-2xl font-black text-slate-900">Waiting for seller</h1>
                        <p className="mb-8 text-center text-sm text-slate-500">
                            Show this code to the seller at the counter to confirm your order.
                        </p>

                        {/* Big order code */}
                        <div className="mb-8 w-full rounded-2xl border-2 border-dashed border-orange-300 bg-orange-50 p-6 text-center">
                            <p className="mb-1 text-xs font-bold uppercase tracking-widest text-orange-400">
                                Your Order Code
                            </p>
                            <p className="font-mono text-5xl font-black tracking-[0.2em] text-slate-900">
                                {initialOrder.code}
                            </p>
                        </div>

                        {/* Order summary */}
                        <div className="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                                Order Summary
                            </p>
                            <div className="space-y-2">
                                {items.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between gap-2">
                                        <span className="line-clamp-1 min-w-0 text-sm text-slate-700">
                                            {item.name}{' '}
                                            <span className="text-slate-400">× {item.qty}</span>
                                        </span>
                                        <span className="shrink-0 text-sm font-bold text-slate-900">
                                            {formatCurrency(item.price * item.qty)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span className="font-bold text-slate-500">Total</span>
                                <span className="text-lg font-black text-orange-500">
                                    {formatCurrency(total)}
                                </span>
                            </div>
                        </div>

                        <p className="mt-6 text-center text-xs text-slate-400">
                            This page auto-updates. Keep it open until confirmed.
                        </p>

                        {/* Action buttons */}
                        <div className="mt-6 flex w-full flex-col gap-3">
                            {showManualApprove && (
                                <button
                                    onClick={handleManualApprove}
                                    disabled={approveLoading}
                                    className="w-full rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white shadow-[0_4px_0_0_#c2410c] transition-all active:translate-y-0.5 active:shadow-none disabled:opacity-60"
                                >
                                    {approveLoading ? 'Processing...' : 'Approve Manually (already paid cash)'}
                                </button>
                            )}
                            <button
                                onClick={handleCancel}
                                disabled={cancelLoading}
                                className="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition-colors hover:border-red-300 hover:text-red-600 disabled:opacity-60"
                            >
                                {cancelLoading ? 'Cancelling...' : 'Cancel Order'}
                            </button>
                            {!showManualApprove && (
                                <p className="text-center text-xs text-slate-400">
                                    Manual approval available in {60 - elapsed}s if cashier system fails.
                                </p>
                            )}
                            {actionError && (
                                <p className="text-center text-xs font-semibold text-red-500">{actionError}</p>
                            )}
                        </div>
                    </div>

                    <footer className="shrink-0 p-4 text-center">
                        <p className="flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400">
                            <QrCode className="h-3.5 w-3.5" /> Powered by qpay
                        </p>
                    </footer>
                </div>
            </div>
        </>
    );
}
