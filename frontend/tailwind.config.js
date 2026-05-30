export default {
  content: ['./index.html', './src/**/*.{vue,js}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        eldorado: {
          ink: '#17202a',
          teal: '#0f766e',
          mint: '#ccfbf1',
          amber: '#b7791f',
          danger: '#b91c1c',
          panel: '#f8fafc'
        }
      },
      boxShadow: {
        panel: '0 8px 24px rgba(15, 23, 42, 0.08)'
      }
    }
  },
  plugins: []
};
