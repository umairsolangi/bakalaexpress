import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', 'sans-serif'],
            },
            colors: {
                primary: {
                    DEFAULT: '#00A651', // Bakala Green
                    dark: '#008c44',
                    light: '#e6f7ef',
                },
                secondary: {
                    DEFAULT: '#1F2937', // Gray 800
                    light: '#4B5563',   // Gray 600
                },
                accent: {
                    DEFAULT: '#F59E0B', // Amber 500
                    hover: '#D97706',   // Amber 600
                },
                gray: {
                    50: '#F9FAFB',
                    100: '#F3F4F6',
                    200: '#E5E7EB',
                    800: '#1F2937',
                    900: '#111827',
                }
            },
        },
    },
    plugins: [],
};
