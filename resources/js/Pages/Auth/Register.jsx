import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Mail, QrCode, Store } from 'lucide-react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register" />

            <div className="flex min-h-screen items-center justify-center bg-slate-50 p-4 selection:bg-orange-500 selection:text-white">
                <div className="w-full max-w-md">
                    <Link href="/" className="mb-8 flex flex-col items-center text-center">
                        <div className="mb-2 flex h-12 w-12 rotate-3 items-center justify-center rounded-xl bg-orange-500 text-white">
                            <QrCode className="h-7 w-7" />
                        </div>
                        <span className="text-3xl font-bold tracking-tight text-black">
                            q<span className="text-orange-500">pay</span>
                        </span>
                    </Link>

                    <div className="rounded-[2rem] border-2 border-black bg-white p-8 shadow-2xl">
                        <h2 className="mb-6 text-center text-2xl font-bold text-black">
                            Register qpay for Free
                        </h2>

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-sm font-bold text-slate-700">
                                    Store Name
                                </label>
                                <div className="relative">
                                    <Store className="absolute left-3 top-3 h-5 w-5 text-slate-400" />
                                    <input
                                        required
                                        type="text"
                                        name="name"
                                        value={data.name}
                                        onChange={(event) => setData('name', event.target.value)}
                                        autoComplete="name"
                                        placeholder="My Awesome Store"
                                        className="w-full rounded-xl border-2 border-slate-200 py-3 pl-10 pr-4 outline-none transition-colors focus:border-orange-500 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-bold text-slate-700">
                                    Email
                                </label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-3 h-5 w-5 text-slate-400" />
                                    <input
                                        required
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        onChange={(event) => setData('email', event.target.value)}
                                        autoComplete="username"
                                        placeholder="email@store.com"
                                        className="w-full rounded-xl border-2 border-slate-200 py-3 pl-10 pr-4 outline-none transition-colors focus:border-orange-500 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.email} className="mt-2" />
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-bold text-slate-700">
                                    Password
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-3 h-5 w-5 text-slate-400" />
                                    <input
                                        required
                                        type="password"
                                        name="password"
                                        value={data.password}
                                        onChange={(event) => setData('password', event.target.value)}
                                        autoComplete="new-password"
                                        placeholder="••••••••"
                                        className="w-full rounded-xl border-2 border-slate-200 py-3 pl-10 pr-4 outline-none transition-colors focus:border-orange-500 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.password} className="mt-2" />
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-bold text-slate-700">
                                    Confirm Password
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-3 h-5 w-5 text-slate-400" />
                                    <input
                                        required
                                        type="password"
                                        name="password_confirmation"
                                        value={data.password_confirmation}
                                        onChange={(event) => setData('password_confirmation', event.target.value)}
                                        autoComplete="new-password"
                                        placeholder="••••••••"
                                        className="w-full rounded-xl border-2 border-slate-200 py-3 pl-10 pr-4 outline-none transition-colors focus:border-orange-500 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.password_confirmation} className="mt-2" />
                            </div>

                            <Button type="submit" variant="dark" size="lg" className="mt-6 w-full" disabled={processing}>
                                {processing ? 'Processing...' : 'Register Now'}
                            </Button>
                        </form>

                        <div className="mt-6 text-center text-sm font-bold text-slate-600">
                            Already have an account?{' '}
                            <Link href={route('login')} className="text-orange-500 underline transition-colors hover:text-black">
                                Sign in here
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}