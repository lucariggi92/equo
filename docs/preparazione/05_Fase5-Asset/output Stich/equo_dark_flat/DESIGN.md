---
name: Equo Dark Flat
colors:
  surface: '#0f131d'
  surface-dim: '#0f131d'
  surface-bright: '#353944'
  surface-container-lowest: '#0a0e18'
  surface-container-low: '#171b26'
  surface-container: '#1c1f2a'
  surface-container-high: '#262a35'
  surface-container-highest: '#313540'
  on-surface: '#dfe2f1'
  on-surface-variant: '#bdcabe'
  inverse-surface: '#dfe2f1'
  inverse-on-surface: '#2c303b'
  outline: '#889489'
  outline-variant: '#3e4941'
  surface-tint: '#74db9d'
  primary: '#74db9d'
  on-primary: '#00391f'
  primary-container: '#3aa36b'
  on-primary-container: '#00311a'
  inverse-primary: '#006d40'
  secondary: '#adc7f7'
  on-secondary: '#133057'
  secondary-container: '#2f4a72'
  on-secondary-container: '#9fb9e8'
  tertiary: '#ffb2b8'
  on-tertiary: '#601020'
  tertiary-container: '#dc707b'
  on-tertiary-container: '#57081a'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#91f8b8'
  primary-fixed-dim: '#74db9d'
  on-primary-fixed: '#002110'
  on-primary-fixed-variant: '#00522f'
  secondary-fixed: '#d6e3ff'
  secondary-fixed-dim: '#adc7f7'
  on-secondary-fixed: '#001b3c'
  on-secondary-fixed-variant: '#2d476f'
  tertiary-fixed: '#ffdadb'
  tertiary-fixed-dim: '#ffb2b8'
  on-tertiary-fixed: '#40000f'
  on-tertiary-fixed-variant: '#7f2835'
  background: '#0f131d'
  on-background: '#dfe2f1'
  surface-variant: '#313540'
typography:
  headline-xl:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-xl-mobile:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 34px
  headline-lg:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
  headline-lg-mobile:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 30px
  headline-md:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-lg:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 14px
    fontWeight: '700'
    lineHeight: 20px
  label-md:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  label-sm:
    fontFamily: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
      sans-serif
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-lg: 1.5rem
  margin: 1rem
  margin-md: 1.5rem
  margin-lg: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

The design system delivers an uncompromising, ultra-clean aesthetic grounded in functional clarity and high-contrast ergonomics. It targets analytical dashboards, data validation engines, and rigorous compliance reporting interfaces where information density and instant legibility take precedence.

The visual direction follows strict **Flat High-Contrast Minimalism**:
- **Zero skeuomorphism:** No drop shadows, inner bevels, or faux-light sources.
- **Zero gradients:** Solid, intentional color blocks provide all depth and status signals.
- **Sharp structure:** Depth is expressed entirely through crisp border boundaries (`#2D4A7A`) and stepped tonal surfaces rather than atmospheric effects.
- **Decisive states:** Interactive surfaces respond with distinct, predictable color state transitions designed for prolonged viewing comfort in dark-mode environments.

## Colors

The palette employs deep navy and slate tones contrasted against an authoritative emerald green accent for key actions and focus triggers.

### Canvas & Structural Surfaces
- **Page Canvas / Header / Footer:** `#0B0F19` (Foundation tone; completely matte, untextured).
- **Surface (Cards, Lists, Field Backgrounds):** `#1A365D`.
- **Structural Borders:** `#2D4A7A` (Consistent 1px stroke used to isolate surfaces from canvas).

### Typography
- **Primary Text:** `#FFFFFF` (Headlines, table content, values, labels).
- **Secondary Text:** `#A0AEC0` (Meta info, subtitles, helper text, timestamps).

### Action & Primary CTA
- **Base Background:** `#38A169` with text `#0B0F19` (Font weight: 700).
- **Hover State:** `#2F855A`.
- **Pressed / Active State:** `#276749`.
- **Disabled State:** Surface `#2D3748` with text `#718096`.
- **Focus Rings & Secondary Links:** `#38A169` (Outline 2px solid, offset 2px).

### Semantic & Status Indicator (Semaforo)
- **Success / Valid:** `#38A169`
- **Warning / Alert:** `#ECC94B`
- **Error / Danger:** `#FC8181`

## Typography

The type scale relies on the platform native `system-ui` cascade to maintain speed, native integration, and pixel-precise rendering across diverse OS environments.

- **Legibility:** Primary text is set to `#FFFFFF`, while secondary content uses `#A0AEC0`.
- **Numbers & Metrics:** Tabular numerals should be enabled (`font-variant-numeric: tabular-nums`) across all data displays, financial summaries, and semaforo status counts to preserve column alignment.
- **Labels:** Button text, navigation tabs, and badge counters use semi-bold to bold weights for clear separation from adjacent body text.

## Layout & Spacing

A structured 12-column responsive fluid grid aligns content across all breakpoints. Layout boundaries are tightly organized to manage complex, information-rich interfaces without visual clutter.

- **Desktop (>= 1024px):** 12 columns, `margin-lg` (2rem / 32px), `gutter-lg` (1.5rem / 24px).
- **Tablet (768px – 1023px):** 8 columns, `margin-md` (1.5rem / 24px), `gutter` (1rem / 16px).
- **Mobile (< 768px):** 4 columns, `margin` (1rem / 16px), `gutter` (1rem / 16px).

### Spacing Rhythm
Vertical and horizontal rhythm adheres strictly to 4px/8px intervals:
- `space-xs` (4px): Icon-to-text spacing and compact tags.
- `space-sm` (8px): Form label spacing, intra-element clusters.
- `space-md` (16px): Input padding, card body padding, list row spacing.
- `space-lg` (24px): Card header padding, card group gaps.
- `space-xl` (32px): Dashboard section spacing.

## Elevation & Depth

This design system is strictly **flat**:
- **No Shadows:** `box-shadow` is set to `none` across all components (cards, dialogs, popovers, dropdowns, and buttons).
- **Surface Differentiation:** Layering is achieved solely via the color transition between the canvas (`#0B0F19`) and elevated container surfaces (`#1A365D`).
- **Border Delineation:** All surfaces placed on top of `#0B0F19` must declare a solid 1px border using `#2D4A7A`.
- **Modals & Overlays:** Modals use `#1A365D` surface fills with a 1px `#2D4A7A` border, placed above a flat, dimming `#0B0F19` backdrop set to 80% opacity without blur filters.

## Shapes

Corner radii establish a functional hierarchy:

- **Cards & Data Panels:** `12px` border radius (`border-radius: 12px`).
- **Interactive Controls (Buttons, Inputs, Selects):** `10px` border radius (`border-radius: 10px`).
- **Pills, Badges & Chips:** Fully rounded caps (`border-radius: 9999px`).
- **Checkboxes:** `4px` border radius (`border-radius: 4px`).
- **Radio Buttons:** `50%` circle (`border-radius: 50%`).

## Components

### Buttons
- **Primary Action:**
  - Background: `#38A169`, Text: `#0B0F19` (Font weight 700).
  - Radius: `10px`. Border: none. Height: `44px` (desktop), `48px` (mobile).
  - Hover: `#2F855A`. Pressed: `#276749`.
  - Disabled: Background `#2D3748`, Text `#718096`, pointer-events disabled.
- **Secondary / Outline Action:**
  - Background: `transparent`, Text: `#FFFFFF`, Border: 1px solid `#2D4A7A`.
  - Hover: Background `#1A365D`, Border: `#38A169`.
- **Focus Ring:** Offset outline `2px solid #38A169` with `2px` offset.

### Input Fields & Controls
- **Text Inputs & Dropdowns:**
  - Background: `#1A365D`, Border: 1px solid `#2D4A7A`, Radius: `10px`.
  - Padding: `12px 16px`, Text: `#FFFFFF`, Placeholder: `#A0AEC0`.
  - Focus state: Border: 1px solid `#38A169`, Outline: 2px solid `#38A169`.
  - Error state: Border: 1px solid `#FC8181`, helper text `#FC8181`.

### Checkboxes & Radio Buttons
- **Unchecked:** Background: `#0B0F19`, Border: 1.5px solid `#2D4A7A`.
- **Checked:** Background: `#38A169`, Check icon: `#0B0F19`.
- Checkbox radius: `4px`. Radio radius: `50%`.

### Cards & Container Panels
- Surface: `#1A365D`, Border: 1px solid `#2D4A7A`, Radius: `12px`.
- Padding: `20px` or `24px`.
- Card Header: Divider border bottom `1px solid #2D4A7A` when separating titles from internal widgets.

### Lists & Tables
- **List Container / Table Wrapper:** Background `#1A365D`, Border `1px solid #2D4A7A`, Radius `12px`, overflow hidden.
- **Row Separation:** Border-bottom `1px solid #2D4A7A`.
- **Row States:** Default `#1A365D`, Hover state `#234574` (flat color shift, no shadows).

### Status Badges & Semaforo Indicators
- **Pill Badges:** Radius `9999px`, padding `4px 12px`, font weight 600, label size `12px`.
  - *Success:* Background `rgba(56, 161, 105, 0.15)`, Border `1px solid #38A169`, Text `#38A169`.
  - *Warning:* Background `rgba(236, 201, 75, 0.15)`, Border `1px solid #ECC94B`, Text `#ECC94B`.
  - *Error:* Background `rgba(252, 129, 129, 0.15)`, Border `1px solid #FC8181`, Text `#FC8181`.
- **Semaforo Indicator Dot:** Solid circle `8px` or `10px` without border: `#38A169` (Success), `#ECC94B` (Warning), `#FC8181` (Error).