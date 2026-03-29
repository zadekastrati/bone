import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    // Touch: first tap was only applying :hover; second tap followed the link/button.
    // Restrict hover styles to devices that actually support hover (see corePlugins hover variant).
    future: {
        hoverOnlyWhenSupported: true,
    },
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                ink: {
                    50: '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f46',
                    800: '#27272a',
                    900: '#18181b',
                    950: '#09090b',
                },
                /* Neutral grey — aligns with ink for a cohesive chrome + UI accent */
                accent: {
                    50: '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f46',
                    800: '#27272a',
                    900: '#18181b',
                    950: '#09090b',
                },
                brand: {
                    50: '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f46',
                    800: '#27272a',
                    900: '#18181b',
                    950: '#09090b',
                },
            },
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
                display: ['Oswald', 'Arial Narrow', 'sans-serif'],
            },
            letterSpacing: {
                mega: '0.2em',
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            boxShadow: {
                soft: '0 1px 2px rgb(9 9 11 / 0.04), 0 8px 24px -6px rgb(9 9 11 / 0.08)',
                'soft-lg': '0 4px 6px -1px rgb(9 9 11 / 0.05), 0 16px 40px -12px rgb(9 9 11 / 0.1)',
                elevated:
                    '0 2px 6px -1px rgb(9 9 11 / 0.05), 0 16px 40px -12px rgb(9 9 11 / 0.09)',
                float: '0 20px 50px -16px rgb(9 9 11 / 0.14), 0 8px 16px -8px rgb(9 9 11 / 0.08)',
                glow: '0 0 60px -12px rgb(113 113 122 / 0.35)',
                inner: 'inset 0 1px 0 0 rgb(255 255 255 / 0.06)',
            },
            transitionTimingFunction: {
                'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            keyframes: {
                marquee: {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(-50%)' },
                },
            },
            animation: {
                marquee: 'marquee 50s linear infinite',
            },
            backgroundImage: {
                'hero-radial':
                    'radial-gradient(ellipse 100% 70% at 50% -10%, rgb(244 244 245 / 0.95), transparent 52%), radial-gradient(ellipse 80% 55% at 100% 0%, rgb(228 228 231 / 0.65), transparent 50%), radial-gradient(ellipse 60% 45% at 0% 100%, rgb(212 212 216 / 0.45), transparent 55%), linear-gradient(180deg, #fafafa 0%, #f4f4f5 38%, #e4e4e7 100%)',
                'card-shine':
                    'linear-gradient(135deg, rgb(255 255 255 / 0.1) 0%, transparent 45%, rgb(255 255 255 / 0.05) 100%)',
                'page-mesh':
                    'linear-gradient(180deg, rgb(250 250 250) 0%, rgb(244 244 245) 40%, rgb(228 228 231) 100%)',
            },
        },
    },
    plugins: [],
};
