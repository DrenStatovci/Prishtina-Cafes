import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js",
  ],
  theme: {
    container: { center: true, padding: '1rem' },
    extend: {
      fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
      colors: {
        brand: {
          50: '#f5f7ff', 100: '#e9edff', 200: '#cfd9ff', 300: '#a8bbff',
          400: '#7a95ff', 500: '#4f6dff', 600: '#3d55db', 700: '#2f41af',
          800: '#26378d', 900: '#222f73',
        }
      },
      boxShadow: {
        soft: '0 6px 24px -10px rgba(0,0,0,.15)',
        card: '0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.10)',
      }
    },
  },
  plugins: [forms, typography],
}