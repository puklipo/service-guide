import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flyonui/dist/js/*.js'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"M PLUS 2"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                blue: colors.indigo,
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        a: {
                            color: theme('colors.indigo.500'),
                            '&:hover': {
                                color: theme('colors.indigo.400'),
                            },
                        },
                    },
                },
            }),
        },
    },

    flyonui: {
        themes: [
            {
                light: {
                    ...require("flyonui/src/theming/themes")["light"],
                    primary: colors.indigo["500"],
                },
                dark: {
                    ...require("flyonui/src/theming/themes")["dark"],
                    primary: colors.indigo["500"],
                    'base-100': '#000',
                }
            }
        ]
    },

    plugins: [
        forms,
        typography,
        require("flyonui"),
        require("flyonui/plugin")
    ],
};
