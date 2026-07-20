/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.ts",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        primary: {
          DEFAULT: '#991B1B', // Tailwind red-800
          dark: '#7F1D1D',    // Tailwind red-900
          light: '#B91C1C',   // Tailwind red-700
        },
        accent: {
          DEFAULT: '#F59E0B',
          dark: '#D97706',
        },
        electric: '#00D4FF',
        neon: '#39FF14',
        dark: {
          DEFAULT: '#0A0E1A',
          card: '#111827',
          surface: '#1F2937',
        }
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
