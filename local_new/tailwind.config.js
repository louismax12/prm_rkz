/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: "class",
  content: ["./*.html", "./*.js"],
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/container-queries'),
  ],
  theme: {
    extend: {
      colors: {
        "on-primary-container": "#dee9ff",
        "on-secondary-container": "#646468",
        "error": "#ba1a1a",
        "surface-container-highest": "#dee3e8",
        "outline": "#717783",
        "on-tertiary-fixed": "#191c1d",
        "tertiary-fixed": "#e1e3e4",
        "surface-dim": "#d6dae0",
        "secondary": "#5e5e62",
        "primary-fixed-dim": "#a5c8ff",
        "surface-container-high": "#e4e9ee",
        "tertiary-fixed-dim": "#c5c7c8",
        "on-secondary-fixed": "#1a1b1e",
        "on-surface": "#171c20",
        "on-primary-fixed": "#001c3a",
        "on-tertiary": "#ffffff",
        "on-error": "#ffffff",
        "on-primary": "#ffffff",
        "surface-container-lowest": "#ffffff",
        "on-secondary": "#ffffff",
        "on-surface-variant": "#414752",
        "secondary-fixed": "#e3e2e6",
        "secondary-fixed-dim": "#c7c6ca",
        "surface-container-low": "#f0f4fa",
        "inverse-on-surface": "#edf1f7",
        "primary": "#005196",
        "inverse-primary": "#a5c8ff",
        "tertiary-container": "#67696a",
        "surface-variant": "#dee3e8",
        "on-tertiary-container": "#e8e9ea",
        "surface-container": "#eaeef4",
        "primary-fixed": "#d4e3ff",
        "on-primary-fixed-variant": "#004785",
        "tertiary": "#4e5152",
        "on-background": "#171c20",
        "secondary-container": "#e3e2e6",
        "surface": "#f6faff",
        "background": "#f6faff",
        "error-container": "#ffdad6",
        "surface-bright": "#f6faff",
        "on-tertiary-fixed-variant": "#454748",
        "inverse-surface": "#2c3135",
        "on-error-container": "#93000a",
        "outline-variant": "#c1c6d4",
        "surface-tint": "#005faf",
        "primary-container": "#0069c0",
        "on-secondary-fixed-variant": "#46474a"
      },
      borderRadius: {
        "DEFAULT": "0.125rem",
        "lg": "0.25rem",
        "xl": "0.5rem",
        "full": "0.75rem"
      },
      spacing: {
        "stack-dense": "4px",
        "stack-compact": "8px",
        "sidebar-width": "240px",
        "gutter": "16px",
        "margin-page": "24px"
      },
      fontFamily: {
        "body-md": ["Plus Jakarta Sans", "sans-serif"],
        "label-sm": ["Plus Jakarta Sans", "sans-serif"],
        "display-sm": ["Plus Jakarta Sans", "sans-serif"],
        "label-md": ["Plus Jakarta Sans", "sans-serif"],
        "headline-md": ["Plus Jakarta Sans", "sans-serif"],
        "body-sm": ["Plus Jakarta Sans", "sans-serif"],
        "mono-data": ["monospace"],
        "headline-sm": ["Plus Jakarta Sans", "sans-serif"]
      },
      fontSize: {
        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
        "label-sm": ["11px", { "lineHeight": "14px", "fontWeight": "500" }],
        "display-sm": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
        "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
        "body-sm": ["13px", { "lineHeight": "18px", "fontWeight": "400" }],
        "mono-data": ["13px", { "lineHeight": "18px", "fontWeight": "400" }],
        "headline-sm": ["16px", { "lineHeight": "24px", "fontWeight": "600" }]
      }
    }
  }
}
