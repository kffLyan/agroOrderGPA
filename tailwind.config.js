import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                // Seluruh nilai di bawah diambil dari palet Tailwind config di
                // resources/views/auth/login.blade.php (Material 3 "GPA Green")
                // dan design system "Dashboard Ringkasan & Operasional Klien B2B".
                canvas: '#f8faf3',
                surface: {
                    DEFAULT: '#ffffff',
                    base: '#f8faf3',
                    muted: '#f0f4e8',
                    shell: '#f2f4ed',
                    sunken: '#e9eddd',
                    track: '#edefe8',
                    raised: '#dfe6cf',
                    disabled: '#e1e3dd',
                    pill: '#e7e9e2',
                },
                ink: {
                    DEFAULT: '#191c18',
                    strong: '#153a01',
                    muted: '#4a5243',
                    subtle: '#6f7a62',
                    body: '#43493d',
                    quiet: '#73796c',
                },
                line: {
                    DEFAULT: '#d7e0c9',
                    strong: '#6f7a62',
                    faint: '#e9eddd',
                    hair: '#e5ebe0',
                    soft: '#e7e9e2',
                    board: '#c3c9b9',
                },
                brand: {
                    DEFAULT: '#153a01',
                    strong: '#153a01',
                    hover: '#122b00',
                    soft: '#dce8cd',
                    deep: '#0c2401',
                    line: '#41682a',
                },
                accent: {
                    DEFAULT: '#bef377',
                    deep: '#aabd06',
                    edge: '#a3d65e',
                },
                success: {
                    DEFAULT: '#4e7a03',
                    soft: '#dde8c6',
                    deep: '#426900',
                    ink: '#467000',
                },
                danger: {
                    DEFAULT: '#ba1a1a',
                    soft: '#ffdad6',
                    ink: '#93000a',
                },
                warning: {
                    DEFAULT: '#492c00',
                    soft: '#ffddb7',
                    wash: '#faf1e1',
                    deep: '#2d1900',
                    ink: '#2a1700',
                    border: '#f7bb71',
                    // Palet "stok terbatas / alokasi kritis" pada design Katalog
                    // Komoditas dan ringkasan Rule 04 di halaman Keranjang.
                    caution: '#704602',
                    cream: '#f5f0e3',
                    row: '#fdf3e4',
                },
            },
            borderRadius: {
                DEFAULT: '0.25rem',
                none: '0px',
                sm: '0.125rem',
                md: '0.375rem',
                lg: '0.5rem',
                xl: '0.75rem',
                '2xl': '1rem',
                full: '9999px',
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', 'Helvetica', 'Arial', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
                inter: ['Inter', 'Helvetica', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                '2xs': ['0.6875rem', { lineHeight: '1rem' }],
            },
            boxShadow: {
                card: '0 2px 6px 0 rgb(0 0 0 / 0.1)',
                pop: '0 12px 28px -16px rgb(0 0 0 / 0.24)',
                sub: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
            },
            maxWidth: {
                shell: '1600px',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(6px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'toast-in': {
                    '0%': { opacity: '0', transform: 'translateY(8px) scale(0.98)' },
                    '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                'pulse-ring': {
                    '0%': { boxShadow: '0 0 0 0 rgb(78 122 3 / 0.35)' },
                    '100%': { boxShadow: '0 0 0 6px rgb(78 122 3 / 0)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .18s ease-out both',
                'toast-in': 'toast-in .18s ease-out both',
                'pulse-ring': 'pulse-ring 1.4s ease-out infinite',
            },
        },
    },

    plugins: [forms],
};