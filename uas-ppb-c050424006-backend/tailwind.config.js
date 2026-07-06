import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                'brand-blue': '#2f6fed',
                'brand-blue-light': '#4f8cff',
                'brand-teal': '#3ec9c0',
                'bg-page': '#f8f9fb',
                'sidebar-active': '#f1f2f4',
                'success-green': '#1fa855',
                'warning-amber': '#f5a623',
                'text-heading': '#1a1a1a',
                'text-body': '#4b5563',
                'text-muted': '#9ca3af',
                'border-subtle': '#e5e7eb',
            },
            borderRadius: {
                'app-card': '18px',
                'app-pill': '9999px',
            },
            boxShadow: {
                'app-card': '0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)',
            },
            backgroundImage: {
                'chart-gradient': 'linear-gradient(180deg, rgba(47,111,237,0.25) 0%, rgba(62,201,192,0.05) 100%)',
                'progress-gradient': 'linear-gradient(90deg, #2f6fed 0%, #3ec9c0 100%)',
                'gradient-button': 'linear-gradient(135deg, #3A7EF8 0%, #2563EB 100%)',
                'gradient-auth-page': 'linear-gradient(135deg, #eaf1ff 0%, #f5f8ff 50%, #ffffff 100%)',
            },
        },
    },

    plugins: [forms],
};
