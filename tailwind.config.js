import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/View/**/*.php',
    ],

    safelist: [
        // Reações dinâmicas
        'bg-blue-500', 'bg-rose-500', 'bg-amber-500', 'bg-orange-500',
        'bg-violet-500', 'bg-yellow-500',
        'text-blue-500', 'text-rose-500', 'text-amber-500', 'text-orange-500',
        'text-violet-500', 'text-yellow-500',
        'ring-blue-500', 'ring-rose-500', 'ring-amber-500', 'ring-orange-500',
        'ring-violet-500', 'ring-yellow-500',
        // Níveis (cores de anel)
        'ring-slate-400', 'ring-green-500', 'ring-sky-500', 'ring-violet-500',
        'ring-orange-500', 'ring-amber-500',
        // Chips
        'bg-emerald-100', 'text-emerald-700',
        'bg-sky-100', 'text-sky-700',
        'bg-amber-100', 'text-amber-700',
        'bg-violet-100', 'text-violet-700',
        'bg-rose-100', 'text-rose-700',
        'bg-slate-100', 'text-slate-700',
    ],

    theme: {
        extend: {
            fontFamily: {
                display: ['"Bricolage Grotesque"', 'sans-serif'],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    DEFAULT: '#22C55E',
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                },
                accent: {
                    DEFAULT: '#F97316',
                    500: '#F97316',
                    600: '#EA580C',
                },
                ink: {
                    DEFAULT: '#0F172A',
                    dark: '#0B1220',
                },
                paper: '#FAFAF7',
            },
            boxShadow: {
                soft: '0 4px 24px -8px rgba(15, 23, 42, 0.08)',
                'soft-lg': '0 10px 40px -12px rgba(15, 23, 42, 0.12)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
        },
    },

    plugins: [forms],
};
