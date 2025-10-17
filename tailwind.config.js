/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
    ],
    theme: {
        extend: {
            gridTemplateColumns: {
                '14': 'repeat(18, minmax(0, 1fr))',
            },
            gridColumn: {
                'span-17': 'span 17 / span 17',
            }
        },
    },
    plugins: [],
    darkMode: ['selector', '.dark'],
    safelist: [
        "hover:bg-[rgb(229,231,235)]!",
        "hover:bg-[#0707a5]!"
    ],
}

