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
          50: '#F8F5F2', 100: '#F1EAE2', 200: '#E3D1C2', 300: '#CFAE97', 400: '#B57D57',
          500: '#8E5A3A', 600: '#7C4A2D', 700: '#653C24', 800: '#4F2F1C', 900: '#3E2416',
        },
        accent: {
          50: '#FFF7E8', 100: '#FFEFD1', 200: '#FFDFA3', 300: '#FBCB75', 400: '#F2AF3A',
          500: '#E2911A', 600: '#C47414', 700: '#9A5710', 800: '#7A440D', 900: '#5E3409',
        },
        leaf: {
          50: '#F3FAF6', 100: '#E2F4EA', 200: '#C4E7D5', 300: '#9AD4B6', 400: '#6FBF97',
          500: '#4AA77E', 600: '#2F8E66', 700: '#237254', 800: '#1C5A44', 900: '#174A39',
        },
        cream: { '50': '#FAF7F2' },
        ink: { '900': '#1F2937' }
      },
      boxShadow: {
        soft: '0 6px 24px -10px rgba(0,0,0,.15)',
        card: '0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.10)',
      }
    },
  },
  plugins: [forms, typography],
}