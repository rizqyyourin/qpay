import { Slot } from '@radix-ui/react-slot';
import { cva } from 'class-variance-authority';

import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-bold transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-orange-500 text-white shadow-[0_6px_0_0_#c2410c] hover:-translate-y-1 hover:bg-black hover:shadow-[0_8px_0_0_#000000]',
                secondary: 'border-2 border-black bg-white text-black hover:bg-slate-50',
                dark: 'bg-black text-white shadow-[0_6px_0_0_rgba(0,0,0,0.35)] hover:-translate-y-1 hover:bg-orange-500',
                ghost: 'bg-transparent text-slate-700 hover:bg-slate-100 hover:text-slate-950',
            },
            size: {
                default: 'h-12 px-6 py-3',
                lg: 'h-14 px-8 py-4 text-base',
                sm: 'h-10 rounded-xl px-4',
                icon: 'h-10 w-10 rounded-xl',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

function Button({ className, variant, size, asChild = false, ...props }) {
    const Comp = asChild ? Slot : 'button';

    return (
        <Comp
            className={cn(buttonVariants({ variant, size, className }))}
            {...props}
        />
    );
}

export { Button, buttonVariants };