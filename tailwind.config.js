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
                /**
                 * Warm ink — text and surfaces (brown-nude greige, not cool zinc).
                 */
                ink: {
                    50: '#f8f5f1',
                    100: '#eee6de',
                    200: '#ddd0c2',
                    300: '#c4b19f',
                    400: '#a58d78',
                    500: '#866f5d',
                    600: '#6c584a',
                    700: '#58473c',
                    800: '#493b32',
                    900: '#3a2f28',
                    950: '#17120f',
                },
                /**
                 * Accent — buttons, links, focus (dusty camel / nude terracotta).
                 */
                accent: {
                    50: '#f6efe8',
                    100: '#e9daca',
                    200: '#d6c0ab',
                    300: '#bf9f83',
                    400: '#a78163',
                    500: '#8f674b',
                    600: '#76533d',
                    700: '#5f4332',
                    800: '#4f3729',
                    900: '#422e22',
                    950: '#21160f',
                },
                brand: {
                    50: '#f7f2ec',
                    100: '#eadfd2',
                    200: '#dac8b6',
                    300: '#c1a78d',
                    400: '#a7886d',
                    500: '#8d6d55',
                    600: '#745744',
                    700: '#604739',
                    800: '#503b30',
                    900: '#403028',
                    950: '#201711',
                },
                /**
                 * Overrides default zinc so existing `zinc-*` utilities read warm/nude.
                 */
                zinc: {
                    50: '#f7f4f0',
                    100: '#efe8e0',
                    200: '#e1d7cb',
                    300: '#cabdaf',
                    400: '#ab9a88',
                    500: '#8c7c6a',
                    600: '#716353',
                    700: '#5c4f42',
                    800: '#4b4136',
                    900: '#3d342c',
                    950: '#1b1713',
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
                soft: '0 1px 2px rgb(56 45 36 / 0.05), 0 10px 26px -10px rgb(56 45 36 / 0.11)',
                'soft-lg': '0 4px 8px -2px rgb(56 45 36 / 0.08), 0 20px 44px -16px rgb(56 45 36 / 0.14)',
                elevated:
                    '0 2px 8px -2px rgb(56 45 36 / 0.08), 0 16px 42px -14px rgb(56 45 36 / 0.13)',
                float: '0 24px 56px -18px rgb(56 45 36 / 0.17), 0 8px 18px -8px rgb(56 45 36 / 0.1)',
                glow: '0 0 56px -14px rgb(146 111 84 / 0.26)',
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
                    'radial-gradient(ellipse 100% 70% at 50% -10%, rgb(248 243 236 / 0.95), transparent 52%), radial-gradient(ellipse 80% 55% at 100% 0%, rgb(221 201 181 / 0.58), transparent 52%), radial-gradient(ellipse 60% 45% at 0% 100%, rgb(192 166 142 / 0.38), transparent 58%), linear-gradient(180deg, #f7f2ec 0%, #eee6de 40%, #ddd0c2 100%)',
                'card-shine':
                    'linear-gradient(135deg, rgb(255 255 255 / 0.12) 0%, transparent 45%, rgb(255 255 255 / 0.05) 100%)',
                'page-mesh':
                    'linear-gradient(180deg, rgb(247 244 240) 0%, rgb(238 230 221) 44%, rgb(221 208 193) 100%)',
            },
        },
    },
    plugins: [],
};
