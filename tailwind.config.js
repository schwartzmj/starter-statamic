const colors = require("tailwindcss/colors");
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.antlers.html",
        "./resources/**/*.antlers.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.vue",
        "./content/**/*.md",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    ...colors.orange,
                },
                complimentary: {
                    ...colors.sky,
                },
            },
            fontFamily: {
                body: ["Inter Variable", "sans-serif"],
                heading: ["Inter Variable", "sans-serif"],
            },
        },
    },

    plugins: [require("@tailwindcss/typography")],
};
