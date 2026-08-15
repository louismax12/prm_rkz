---
name: Clinical Precision
colors:
  surface: '#f6faff'
  surface-dim: '#d6dae0'
  surface-bright: '#f6faff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f4fa'
  surface-container: '#eaeef4'
  surface-container-high: '#e4e9ee'
  surface-container-highest: '#dee3e8'
  on-surface: '#171c20'
  on-surface-variant: '#414752'
  inverse-surface: '#2c3135'
  inverse-on-surface: '#edf1f7'
  outline: '#717783'
  outline-variant: '#c1c6d4'
  surface-tint: '#005faf'
  primary: '#005196'
  on-primary: '#ffffff'
  primary-container: '#0069c0'
  on-primary-container: '#dee9ff'
  inverse-primary: '#a5c8ff'
  secondary: '#5e5e62'
  on-secondary: '#ffffff'
  secondary-container: '#e3e2e6'
  on-secondary-container: '#646468'
  tertiary: '#4e5152'
  on-tertiary: '#ffffff'
  tertiary-container: '#67696a'
  on-tertiary-container: '#e8e9ea'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d4e3ff'
  primary-fixed-dim: '#a5c8ff'
  on-primary-fixed: '#001c3a'
  on-primary-fixed-variant: '#004785'
  secondary-fixed: '#e3e2e6'
  secondary-fixed-dim: '#c7c6ca'
  on-secondary-fixed: '#1a1b1e'
  on-secondary-fixed-variant: '#46474a'
  tertiary-fixed: '#e1e3e4'
  tertiary-fixed-dim: '#c5c7c8'
  on-tertiary-fixed: '#191c1d'
  on-tertiary-fixed-variant: '#454748'
  background: '#f6faff'
  on-background: '#171c20'
  surface-variant: '#dee3e8'
typography:
  display-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
  mono-data:
    fontFamily: monospace
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  sidebar-width: 240px
  gutter: 16px
  margin-page: 24px
  stack-compact: 8px
  stack-dense: 4px
---

## Brand & Style

The design system focuses on **Clinical Precision**, prioritizing high-density information architecture for medical professionals. The brand personality is authoritative, reliable, and meticulously organized, reflecting the critical nature of rehabilitation data management. 

The aesthetic follows a **Modern Corporate** approach with a focus on utility. It utilizes a structured, "flat-plus" style: primarily flat surfaces with subtle depth used only to indicate interactivity or information hierarchy. The interface is designed to reduce cognitive load in high-stress environments through clear grouping, logical sequencing, and a "content-first" philosophy. Whitespace is used functionally to separate complex data sets rather than for purely decorative purposes.

## Colors

The palette is strictly functional, utilizing high-contrast pairings to ensure legibility under clinical lighting conditions.

- **Primary Blue (#0069C0):** Used exclusively for primary actions, active states, and focus indicators. It provides a clear "path of least resistance" for the user.
- **Surface & Background:** Pure White (#FFFFFF) is used for all card containers and input fields to maximize contrast. Soft Background Gray (#F8F9FA) provides a subtle foundation for the layout, distinguishing the canvas from the content.
- **Typography:** Dark Charcoal (#202124) serves as the primary ink color for maximum readability. A neutral gray (#70757A) is reserved for secondary metadata and disabled states.
- **Functional Colors:** Success (Green), Warning (Amber), and Error (Red) should be used sparingly for status indicators within data tables and clinical alerts.

## Typography

Typography is optimized for high-density data consumption. **Plus Jakarta Sans** provides a clean, modern geometric feel that remains legible at small sizes.

- **Scale:** A compact typographic scale is employed to allow more information on screen without sacrificing clarity.
- **Body Text:** 14px is the standard for clinical notes and data entries. 13px is used for dense tables.
- **Labels:** Uppercase or semi-bold labels are used for form headers and table headers to provide a clear distinction from user-generated data.
- **Monospace Fallback:** For numeric medical values or ID codes, a system monospace font may be used to ensure character alignment in tables.

## Layout & Spacing

The layout is a **Fixed-Fluid Hybrid** designed for wide-screen clinical monitors.

- **Sidebar:** A permanent 240px left sidebar anchors the navigation. It uses a slightly darker shade or a subtle border to separate from the main workspace.
- **Workspace:** The main content area uses 24px padding for general pages and 16px padding for high-density clinical modules to maximize screen real estate.
- **Grid:** A 12-column grid is used within the workspace. Clinical modules often utilize a **Split-Screen** layout (50/50 or 40/60) to allow for simultaneous viewing of patient history and current data entry.
- **Density:** High-density vertical rhythm (4px/8px increments) is used for form fields and list items to minimize the need for scrolling during patient assessments.

## Elevation & Depth

This design system uses **Tonal Layers** and **Low-Contrast Outlines** instead of heavy shadows.

- **Level 0 (Background):** #F8F9FA.
- **Level 1 (Cards/Surface):** Pure White #FFFFFF with a 1px border (#E0E0E0). No shadow.
- **Level 2 (Interactive/Floating):** Subtle ambient shadow (Y: 2px, Blur: 4px, 5% opacity) reserved for dropdowns, tooltips, and active modal dialogs.
- **Dividers:** 1px solid #EEEEEE used to separate rows in data tables and sections in sidebars.

## Shapes

The shape language is **Soft and Professional**. A consistent 4px (0.25rem) radius is applied to almost all UI elements including buttons, input fields, and card containers.

- **Buttons/Inputs:** 4px radius (Soft).
- **Large Containers:** 8px radius (Large) for major clinical modules or dashboard widgets.
- **Status Badges:** Fully pill-shaped (rounded-full) to distinguish them from interactive buttons.

## Components

### Data Tables
Tables are the core of the ERP. Use a 40px row height for high density. Headers should be sticky with a #F8F9FA background and 1px bottom border. Hover states on rows should use a very pale blue tint (#F0F7FF).

### Buttons & Inputs
- **Primary Button:** Solid #0069C0 with white text.
- **Secondary Button:** White background with #0069C0 border and text.
- **Search Bar:** Leading search icon, trailing "Filter" or "Search" button attached to the input field for a unified look.

### Clinical Widgets
Small, self-contained cards for patient metrics (e.g., heart rate, mobility score). These should use `label-sm` for titles and `display-sm` for the primary value.

### Step-by-Step Wizards
Horizontal progress indicators at the top of multi-page clinical assessments. Use a "connector line" style: completed steps are Primary Blue, current step is outlined Blue, and future steps are Gray.

### Split-Screen Modules
Used for "Review and Act" workflows. The left panel contains read-only patient history, and the right panel contains the active entry form. Both panels should have independent scrollbars.