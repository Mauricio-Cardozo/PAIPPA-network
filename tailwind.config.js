import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Verde de la huerta: color principal de la marca.
                verde: {
                    50: '#f1f8f2',
                    100: '#dceedf',
                    200: '#bbddc1',
                    300: '#8ec49a',
                    400: '#5ea56f',
                    500: '#3d8a51',
                    600: '#2c6e3f',
                    700: '#245834',
                    800: '#1f472c',
                    900: '#1a3b25',
                },
                // Naranja zapallo: acentos y llamadas a la acción secundarias.
                zapallo: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    500: '#f08a24',
                    600: '#d9700f',
                    700: '#b4570c',
                },
            },
        },
    },

    plugins: [forms],
};
