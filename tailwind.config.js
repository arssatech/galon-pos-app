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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
// tailwind.config.js
// https://tailwindcss.com/docs/configuration
// https://tailwindcss.com/docs/installation
// https://tailwindcss.com/docs/adding-custom-fonts
// https://tailwindcss.com/docs/adding-custom-colors
// https://tailwindcss.com/docs/adding-custom-spacing
// https://tailwindcss.com/docs/adding-custom-breakpoints
// https://tailwindcss.com/docs/adding-custom-utilities
// https://tailwindcss.com/docs/adding-custom-variants
// https://tailwindcss.com/docs/adding-custom-plugins
// https://tailwindcss.com/docs/adding-custom-forms
// https://tailwindcss.com/docs/adding-custom-typography
// https://tailwindcss.com/docs/adding-custom-aspect-ratio
// https://tailwindcss.com/docs/adding-custom-line-clamp