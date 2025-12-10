/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./resources/**/*.{js,ts,jsx,tsx,blade.php}",
    "./node_modules/daisyui/dist/**/*.js",
    "./node_modules/daisyui/dist/**/*.css",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["light", "dark"],
    darkMode: "class",
  },
}
