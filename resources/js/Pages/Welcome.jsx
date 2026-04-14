import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    Calculator,
    Loader2,
    Menu,
    Package,
    QrCode,
    Sparkles,
    Store,
    Wand2,
} from 'lucide-react';
import { useMemo, useState } from 'react';

import { Button } from '@/Components/ui/button';

const features = [
    {
        icon: Package,
        title: '1. Tambah Produk',
        description:
            'Foto produkmu, masukkan nama dan harga. Semudah bikin status. Katalog toko langsung siap dipakai jualan.',
        tone: 'orange',
    },
    {
        icon: QrCode,
        title: '2. Generate QR Code',
        description:
            'Setiap produk punya QR unik untuk scan product as you go. Pembeli ambil barang, scan, lalu lanjut checkout.',
        tone: 'dark',
    },
    {
        icon: Calculator,
        title: '3. POS & Transaksi',
        description:
            'Kasir tetap cepat, pembeli tetap praktis. Total, kupon, dan struk digital tampil rapi dalam satu alur.',
        tone: 'orange',
    },
];

const promoTemplates = [
    'Lagi cari {name} yang bikin balik lagi? Ini dia favorit pelanggan hari ini. Wajib masuk keranjang sekarang juga! 🔥☕',
    '{name} lagi siap jadi menu andalan toko kamu. Rasanya mantap, tampilannya cantik, dan cocok banget buat repeat order. ✨🛍️',
    'Buat yang mau jualan makin laris, {name} ini wajib naik ke etalase depan. Wangi, menggoda, dan auto bikin penasaran. 🤎🚀',
];

function MockPreview({ productName }) {
    return (
        <div className="mb-4 w-full max-w-[220px] rounded-[1.75rem] border border-orange-100 bg-gradient-to-br from-orange-100 via-white to-yellow-100 p-3 shadow-sm">
            <div className="flex aspect-square items-center justify-center rounded-[1.35rem] border border-white/80 bg-white/80 px-4 text-center shadow-inner">
                <div>
                    <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-black text-white shadow-[0_6px_0_0_rgba(249,115,22,0.35)]">
                        <Package className="h-7 w-7" />
                    </div>
                    <p className="text-sm font-bold text-slate-900">{productName}</p>
                    <p className="mt-1 text-xs font-semibold uppercase tracking-[0.24em] text-orange-500">
                        AI Visual Preview
                    </p>
                </div>
            </div>
        </div>
    );
}

export default function Welcome({ auth, canLogin, canRegister }) {
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [productInput, setProductInput] = useState('Kopi Susu Gula Aren Spesial');
    const [promoResult, setPromoResult] = useState('');
    const [isGenerating, setIsGenerating] = useState(false);

    const demoLabel = useMemo(() => {
        if (auth?.user) {
            return 'Buka Dashboard';
        }

        return 'Masuk / Daftar';
    }, [auth?.user]);

    const demoHref = auth?.user ? route('dashboard') : route('login');

    const generatePromo = () => {
        if (!productInput.trim()) {
            return;
        }

        setIsGenerating(true);

        window.setTimeout(() => {
            const template =
                promoTemplates[
                    Math.floor(Math.random() * promoTemplates.length)
                ];
            setPromoResult(template.replace('{name}', productInput.trim()));
            setIsGenerating(false);
        }, 1100);
    };

    return (
        <>
            <Head title="qpay" />

            <div className="min-h-screen bg-white text-slate-900 selection:bg-orange-500 selection:text-white">
                <nav className="fixed inset-x-0 top-0 z-50 border-b-2 border-slate-100 bg-white/85 backdrop-blur-xl">
                    <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <a href="#top" className="flex items-center gap-3">
                            <div className="flex h-11 w-11 rotate-3 items-center justify-center rounded-2xl bg-orange-500 text-white transition-transform hover:rotate-6">
                                <QrCode className="h-6 w-6" />
                            </div>
                            <span className="text-3xl font-bold tracking-tight text-black">
                                q<span className="text-orange-500">pay</span>
                            </span>
                        </a>

                        <div className="hidden items-center gap-8 md:flex">
                            <a href="#fitur" className="text-sm font-bold text-slate-600 transition-colors hover:text-black">
                                Fitur
                            </a>
                            <a href="#cara-kerja" className="text-sm font-bold text-slate-600 transition-colors hover:text-black">
                                Cara Kerja
                            </a>
                            <a href="#harga" className="text-sm font-bold text-slate-600 transition-colors hover:text-black">
                                Harga
                            </a>
                            {auth?.user ? (
                                <Button asChild variant="dark">
                                    <Link href={route('dashboard')}>Dashboard</Link>
                                </Button>
                            ) : (
                                canLogin && (
                                    <Button asChild variant="dark">
                                        <Link href={route('login')}>{demoLabel}</Link>
                                    </Button>
                                )
                            )}
                        </div>

                        <button
                            type="button"
                            className="inline-flex h-11 w-11 items-center justify-center rounded-2xl text-slate-900 transition hover:bg-slate-100 md:hidden"
                            onClick={() => setIsMobileMenuOpen((open) => !open)}
                        >
                            <Menu className="h-6 w-6" />
                        </button>
                    </div>

                    {isMobileMenuOpen ? (
                        <div className="border-t-2 border-slate-100 bg-white px-4 py-5 shadow-xl md:hidden">
                            <div className="flex flex-col gap-3">
                                <a href="#fitur" className="rounded-2xl px-4 py-3 text-base font-bold text-slate-800 transition hover:bg-slate-100">
                                    Fitur
                                </a>
                                <a href="#cara-kerja" className="rounded-2xl px-4 py-3 text-base font-bold text-slate-800 transition hover:bg-slate-100">
                                    Cara Kerja
                                </a>
                                <a href="#harga" className="rounded-2xl px-4 py-3 text-base font-bold text-slate-800 transition hover:bg-slate-100">
                                    Harga
                                </a>
                                {auth?.user ? (
                                    <Button asChild className="mt-2 w-full" variant="default">
                                        <Link href={route('dashboard')}>Dashboard</Link>
                                    </Button>
                                ) : null}
                                {!auth?.user && canLogin ? (
                                    <Button asChild className="mt-2 w-full" variant="default">
                                        <Link href={route('login')}>{demoLabel}</Link>
                                    </Button>
                                ) : null}
                                {!auth?.user && canRegister ? (
                                    <Button asChild className="w-full" variant="secondary">
                                        <Link href={route('register')}>Daftar Gratis</Link>
                                    </Button>
                                ) : null}
                            </div>
                        </div>
                    ) : null}
                </nav>

                <section id="top" className="relative overflow-hidden pb-20 pt-32 lg:pb-28 lg:pt-40">
                    <div className="animate-blob absolute right-8 top-20 h-64 w-64 rounded-full bg-orange-200/70 blur-3xl" />
                    <div className="animate-blob animation-delay-2000 absolute left-6 top-44 h-72 w-72 rounded-full bg-yellow-200/60 blur-3xl" />

                    <div className="relative mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-8 lg:px-8">
                        <div className="max-w-2xl animate-slide-up-fade">
                            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-100 px-4 py-2 text-sm font-bold text-orange-600">
                                <Store className="h-4 w-4" />
                                <span>Solusi Kasir Pintar UMKM</span>
                            </div>
                            <h1 className="text-balance text-5xl font-bold leading-tight text-black lg:text-6xl">
                                Jualan Makin Sat Set Pakai{' '}
                                <span className="inline-block -rotate-2 text-orange-500">
                                    QR Code!
                                </span>
                            </h1>
                            <p className="mt-6 max-w-xl text-xl font-medium leading-relaxed text-slate-600">
                                Aplikasi kasir yang paling ngerti pedagang toko. Tambah produk,
                                otomatis bikin QR code, bantu scan produk sambil jalan, dan siap
                                dipakai untuk fitur AI promosi.
                            </p>

                            <div className="mt-8 flex flex-col gap-4 sm:flex-row">
                                <Button asChild size="lg">
                                    <Link href={demoHref}>
                                        Mulai Sekarang <ArrowRight className="ml-2 h-5 w-5" />
                                    </Link>
                                </Button>
                                {canRegister && !auth?.user ? (
                                    <Button asChild size="lg" variant="secondary">
                                        <Link href={route('register')}>Lihat Demo</Link>
                                    </Button>
                                ) : (
                                    <Button asChild size="lg" variant="secondary">
                                        <Link href={route('dashboard')}>Lihat Demo</Link>
                                    </Button>
                                )}
                            </div>

                            <div className="mt-10 flex items-center gap-4 text-sm font-semibold text-slate-500">
                                <div className="flex -space-x-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-slate-200 text-xs font-bold text-slate-600">
                                        A
                                    </div>
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-orange-200 text-xs font-bold text-orange-600">
                                        B
                                    </div>
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-yellow-200 text-xs font-bold text-yellow-700">
                                        C
                                    </div>
                                </div>
                                <p>Dipercaya oleh 5.000+ toko di Indonesia</p>
                            </div>
                        </div>

                        <div className="relative mx-auto w-full max-w-md animate-slide-up-fade">
                            <div className="absolute inset-0 rotate-6 rounded-[2.5rem] bg-orange-500" />
                            <div className="relative -rotate-2 rounded-[2.5rem] border-4 border-black bg-black p-4 shadow-2xl transition-transform duration-500 hover:rotate-0">
                                <div className="relative flex h-[600px] flex-col overflow-hidden rounded-[2rem] bg-white">
                                    <div className="rounded-b-[2rem] bg-orange-500 p-6 pb-10 text-white">
                                        <div className="mb-6 flex items-center justify-between">
                                            <span className="text-xl font-bold">qpay POS</span>
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                                                <Menu className="h-4 w-4" />
                                            </div>
                                        </div>
                                        <p className="text-sm text-orange-100">Total Penjualan Hari Ini</p>
                                        <p className="text-3xl font-bold">Rp 1.250.000</p>
                                    </div>

                                    <div className="-mt-6 flex-1 space-y-4 rounded-t-[2rem] bg-slate-50 p-4">
                                        <div className="flex items-center gap-4 rounded-[1.5rem] border border-slate-100 bg-white p-4 shadow-sm">
                                            <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100">
                                                <QrCode className="h-8 w-8 text-black" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-slate-950">Kopi Susu Gula Aren</h4>
                                                <p className="font-bold text-orange-500">Rp 18.000</p>
                                                <p className="text-xs text-slate-400">Scan QR untuk bayar</p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4 rounded-[1.5rem] border border-slate-100 bg-white p-4 opacity-75 shadow-sm">
                                            <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100">
                                                <Package className="h-8 w-8 text-slate-400" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-slate-700">Roti Bakar Coklat</h4>
                                                <p className="font-bold text-orange-500">Rp 15.000</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="absolute bottom-6 left-6 right-6">
                                        <div className="flex items-center justify-between rounded-[1.5rem] bg-black p-4 text-white shadow-lg">
                                            <span className="font-bold">Total (1 item)</span>
                                            <button
                                                type="button"
                                                className="rounded-xl bg-orange-500 px-4 py-2 text-sm font-bold text-white"
                                            >
                                                Bayar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="fitur" className="border-y-2 border-slate-100 bg-slate-50 py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mx-auto mb-16 max-w-3xl text-center">
                            <h2 className="text-4xl font-bold text-black">
                                Satu Aplikasi, Semua Kebutuhan Toko
                            </h2>
                            <p className="mt-4 text-xl font-medium text-slate-600">
                                Tinggalkan cara lama. Kelola produk, bikin QR code, dan layani
                                pelanggan dengan sistem POS yang ringan tapi tetap berkarakter.
                            </p>
                        </div>

                        <div className="grid gap-8 md:grid-cols-3">
                            {features.map((feature) => {
                                const Icon = feature.icon;

                                return (
                                    <article
                                        key={feature.title}
                                        className="group rounded-[2rem] border-2 border-slate-100 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-orange-100/60"
                                    >
                                        <div
                                            className={
                                                feature.tone === 'dark'
                                                    ? 'mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-black transition-transform group-hover:-rotate-3 group-hover:scale-110'
                                                    : 'mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100 text-orange-500 transition-transform group-hover:rotate-3 group-hover:scale-110'
                                            }
                                        >
                                            <Icon className="h-8 w-8" />
                                        </div>
                                        <h3 className="text-2xl font-bold text-black">{feature.title}</h3>
                                        <p className="mt-3 text-base font-medium leading-relaxed text-slate-600">
                                            {feature.description}
                                        </p>
                                    </article>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section className="relative overflow-hidden border-y-2 border-orange-100 bg-orange-50 py-20">
                    <div className="absolute -left-32 top-10 h-64 w-64 rounded-full bg-orange-200/70 blur-3xl" />

                    <div className="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col gap-12 rounded-[2.5rem] border-2 border-orange-100 bg-white p-8 shadow-2xl md:p-12 lg:flex-row lg:items-center">
                            <div className="lg:w-1/2">
                                <div className="mb-6 inline-flex items-center gap-2 rounded-full bg-orange-100 px-4 py-2 text-sm font-bold text-orange-600">
                                    <Sparkles className="h-4 w-4" />
                                    <span>Fitur Cerdas AI</span>
                                </div>
                                <h2 className="text-4xl font-bold text-black">
                                    Bingung Bikin Kata-kata Promosi?
                                </h2>
                                <p className="mt-4 text-xl font-medium text-slate-600">
                                    Tinggal ketik nama produkmu, lalu qpay bikin simulasi caption
                                    promo yang siap kamu olah lagi nanti ke backend AI beneran.
                                </p>

                                <div className="mt-8 flex -space-x-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full border-4 border-white bg-orange-100 text-xl">
                                        🚀
                                    </div>
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full border-4 border-white bg-yellow-100 text-xl">
                                        🔥
                                    </div>
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full border-4 border-white bg-slate-100 text-xl">
                                        💰
                                    </div>
                                </div>
                            </div>

                            <div className="w-full lg:w-1/2">
                                <div className="rounded-[2rem] border-2 border-slate-100 bg-slate-50 p-6">
                                    <label className="mb-2 block text-sm font-bold text-slate-700">
                                        Nama Produk Kamu
                                    </label>
                                    <input
                                        type="text"
                                        value={productInput}
                                        onChange={(event) => setProductInput(event.target.value)}
                                        placeholder="Contoh: Kopi Susu Gula Aren Spesial"
                                        className="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 font-semibold text-slate-800 outline-none transition focus:border-orange-500 focus:ring-0"
                                    />

                                    <Button
                                        type="button"
                                        size="lg"
                                        variant="dark"
                                        className="mt-4 w-full"
                                        onClick={generatePromo}
                                        disabled={isGenerating || !productInput.trim()}
                                    >
                                        {isGenerating ? (
                                            <>
                                                <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                                                Sedang Meracik Keajaiban...
                                            </>
                                        ) : (
                                            <>
                                                <Wand2 className="mr-2 h-5 w-5" />
                                                Buatkan Caption
                                            </>
                                        )}
                                    </Button>

                                    <div className="relative mt-6 flex min-h-[220px] flex-col items-center rounded-2xl border-2 border-slate-100 bg-white p-5">
                                        {!isGenerating && !promoResult ? (
                                            <div className="absolute inset-0 flex items-center justify-center px-6 text-center text-sm font-medium text-slate-400">
                                                Hasil tulisan dan preview visual AI akan muncul di sini...
                                            </div>
                                        ) : null}

                                        {isGenerating ? (
                                            <div className="flex w-full flex-col items-center gap-4 py-4 animate-pulse">
                                                <div className="flex h-32 w-32 items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-100">
                                                    <Package className="h-8 w-8 text-slate-300" />
                                                </div>
                                                <div className="w-full space-y-2">
                                                    <div className="h-4 w-full rounded bg-slate-100" />
                                                    <div className="h-4 w-5/6 rounded bg-slate-100" />
                                                    <div className="mx-auto h-4 w-4/6 rounded bg-slate-100" />
                                                </div>
                                            </div>
                                        ) : null}

                                        {!isGenerating && promoResult ? (
                                            <>
                                                <MockPreview productName={productInput.trim()} />
                                                <p className="w-full whitespace-pre-wrap text-center font-medium text-slate-800">
                                                    {promoResult}
                                                </p>
                                            </>
                                        ) : null}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="cara-kerja" className="relative overflow-hidden bg-black py-24 text-white">
                    <div className="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-orange-500/20 blur-3xl" />

                    <div className="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-16 flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
                            <div className="max-w-xl">
                                <h2 className="text-4xl font-bold">Gimana Cara Mulainya?</h2>
                                <p className="mt-4 text-xl font-medium text-slate-400">
                                    Cuma butuh 3 langkah buat bikin tokomu lebih modern, cepat,
                                    dan siap pakai AI di workflow penjualan.
                                </p>
                            </div>
                        </div>

                        <div className="relative grid gap-8 md:grid-cols-3">
                            <div className="absolute left-[16%] right-[16%] top-12 hidden h-2 overflow-hidden rounded-full bg-slate-900 shadow-[inset_0_1px_3px_rgba(0,0,0,0.8)] md:block">
                                <div className="absolute inset-0 bg-gradient-to-r from-orange-600/30 to-orange-400/30" />
                                <div className="animate-progress-flow absolute inset-y-0 w-1/2 bg-gradient-to-r from-transparent via-white to-transparent opacity-80" />
                            </div>

                            {[
                                {
                                    number: '1',
                                    title: 'Daftar Akun',
                                    text: 'Masuk sebagai seller, siapkan toko, lalu mulai isi katalog tanpa setup yang ribet.',
                                    tone: 'orange',
                                },
                                {
                                    number: '2',
                                    title: 'Buat Katalog',
                                    text: 'Tambah produk, tentukan harga, dan tampilkan QR code produk langsung dari dashboard.',
                                    tone: 'white',
                                },
                                {
                                    number: '3',
                                    title: 'Mulai Jualan!',
                                    text: 'Pembeli scan barang sambil jalan, kasir cek total, lalu transaksi selesai dengan cepat.',
                                    tone: 'orange',
                                },
                            ].map((step) => (
                                <div key={step.number} className="relative z-10 mt-8 flex flex-col items-center text-center md:mt-0">
                                    <div
                                        className={
                                            step.tone === 'white'
                                                ? 'mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl font-bold text-black shadow-[0_0_30px_rgba(255,255,255,0.2)] transition-transform duration-300 hover:-translate-y-2 hover:scale-110'
                                                : 'mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-orange-500 text-3xl font-bold text-black shadow-[0_0_30px_rgba(249,115,22,0.4)] transition-transform duration-300 hover:-translate-y-2 hover:scale-110'
                                        }
                                    >
                                        {step.number}
                                    </div>
                                    <h4 className="text-2xl font-bold">{step.title}</h4>
                                    <p className="mt-2 px-4 font-medium text-slate-400">{step.text}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section id="harga" className="relative overflow-hidden bg-orange-500 py-20 text-black">
                    <div className="bg-grid-dots absolute inset-y-0 left-0 w-64 opacity-20" />
                    <div className="bg-grid-dots absolute inset-y-0 right-0 w-64 opacity-20" />

                    <div className="relative z-10 mx-auto max-w-4xl px-4 text-center">
                        <h2 className="text-4xl font-bold md:text-5xl">
                            Siap Bikin Tokomu Lebih Canggih?
                        </h2>
                        <p className="mx-auto mt-6 max-w-2xl text-xl font-medium text-black/80">
                            Tinggalkan buku catatan manual. Mulai kelola usahamu secara digital
                            bersama ribuan UMKM lainnya hari ini.
                        </p>
                        <div className="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                            <Button asChild size="lg" variant="dark">
                                <Link href={demoHref}>Daftar qpay Sekarang</Link>
                            </Button>
                        </div>
                        <p className="mt-6 text-sm font-bold text-black/60">
                            *Gratis 100% untuk UMKM kecil.
                        </p>
                    </div>
                </section>

                <footer className="border-t-2 border-slate-100 bg-white pb-8 pt-16">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-12 grid gap-12 md:grid-cols-4">
                            <div className="md:col-span-2">
                                <div className="mb-4 flex items-center gap-2">
                                    <div className="flex h-8 w-8 rotate-3 items-center justify-center rounded-lg bg-orange-500 text-white">
                                        <QrCode className="h-5 w-5" />
                                    </div>
                                    <span className="text-2xl font-bold tracking-tight text-black">
                                        q<span className="text-orange-500">pay</span>
                                    </span>
                                </div>
                                <p className="max-w-sm font-medium text-slate-600">
                                    Sahabat sejati pedagang UMKM. Aplikasi POS yang bikin tambah
                                    produk, QR code, dan kasir semudah tepuk tangan.
                                </p>
                            </div>

                            <div>
                                <h4 className="mb-4 text-lg font-bold text-black">Produk</h4>
                                <ul className="space-y-3 font-medium text-slate-600">
                                    <li>Fitur POS</li>
                                    <li>Generator QR Code</li>
                                    <li>Manajemen Stok</li>
                                    <li>Harga</li>
                                </ul>
                            </div>

                            <div>
                                <h4 className="mb-4 text-lg font-bold text-black">Bantuan</h4>
                                <ul className="space-y-3 font-medium text-slate-600">
                                    <li>Pusat Bantuan</li>
                                    <li>Panduan Video</li>
                                    <li>Hubungi CS</li>
                                    <li>Komunitas UMKM</li>
                                </ul>
                            </div>
                        </div>

                        <div className="flex flex-col items-center justify-between gap-4 border-t-2 border-slate-100 pt-8 text-sm font-medium text-slate-500 md:flex-row">
                            <p>© {new Date().getFullYear()} qpay Indonesia. All rights reserved.</p>
                            <div className="flex gap-6">
                                <span>Privasi</span>
                                <span>Syarat & Ketentuan</span>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}