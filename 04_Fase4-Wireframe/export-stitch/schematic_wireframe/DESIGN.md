---
name: Schematic Wireframe
colors:
  surface: '#121415'
  surface-dim: '#121415'
  surface-bright: '#38393a'
  surface-container-lowest: '#0c0e0f'
  surface-container-low: '#1a1c1d'
  surface-container: '#1e2021'
  surface-container-high: '#282a2b'
  surface-container-highest: '#333536'
  on-surface: '#e2e2e3'
  on-surface-variant: '#c8c5cb'
  inverse-surface: '#e2e2e3'
  inverse-on-surface: '#2f3132'
  outline: '#919095'
  outline-variant: '#47464b'
  surface-tint: '#c8c5ca'
  primary: '#c8c5ca'
  on-primary: '#303033'
  primary-container: '#18181b'
  on-primary-container: '#828184'
  inverse-primary: '#5f5e61'
  secondary: '#c6c5cf'
  on-secondary: '#2f3038'
  secondary-container: '#4a4b53'
  on-secondary-container: '#bcbbc5'
  tertiary: '#c6c6ca'
  on-tertiary: '#2f3034'
  tertiary-container: '#16181b'
  on-tertiary-container: '#808185'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#e4e1e6'
  primary-fixed-dim: '#c8c5ca'
  on-primary-fixed: '#1b1b1e'
  on-primary-fixed-variant: '#47464a'
  secondary-fixed: '#e3e1ec'
  secondary-fixed-dim: '#c6c5cf'
  on-secondary-fixed: '#1a1b22'
  on-secondary-fixed-variant: '#46464e'
  tertiary-fixed: '#e2e2e6'
  tertiary-fixed-dim: '#c6c6ca'
  on-tertiary-fixed: '#1a1c1f'
  on-tertiary-fixed-variant: '#45474a'
  background: '#121415'
  on-background: '#e2e2e3'
  surface-variant: '#333536'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
  headline-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  headline-sm:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 22px
  body-lg:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 22px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 18px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 0.75rem
  margin: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-lg: 1rem
  space-xl: 1.5rem
---

## Brand & Style

The design system establishes a disciplined, distraction-free environment tailored strictly for mobile UX prototyping, structural flows, and rapid user-testing validation. The emotional tone is deliberate, analytical, and utilitarian. By stripping away brand expressiveness, decoration, and sensory polish, the interface forces product teams and stakeholders to evaluate information architecture, navigational hierarchy, and interaction mechanics.

The aesthetic philosophy aligns strictly with functional low-fidelity wireframing:
- **Zero Decorative Noise:** Total omission of brand colors, rich gradients, bespoke iconography, or emotive imagery.
- **Structural Blueprinting:** Components represent layout containers, data placeholders, content blocks, and functional pathways.
- **Precision Skeletal UX:** Relies strictly on clear typographic weight, calibrated grayscale tone stepping, and explicit borders to demarcate touch targets and structural zoning.

## Colors

The color palette is strictly achromatic, leveraging calibrated neutral tones optimized for a dark-mode wireframing environment. Every shade serves an explicit functional wireframing role without decorative intent:

- **Canvas Background (`#18181b`):** Base mobile screen surface, establishing maximum baseline contrast for layouts and structural overlays in a dark context.
- **Primary Surface Container (`#27272a`):** Reserved for primary interactive cards, form field fills, content card backgrounds, and standard wireframe boxes.
- **Secondary Surface Container (`#3f3f46`):** Used for elevated containers, nested list rows, inactive states, and structural placeholder zones.
- **Structural Border (`#52525b`):** Defines bounding boxes for cards, dividers, component outlines, and default input boundaries.
- **Emphasized Border / Inactive Control (`#71717a`):** Distinguishes focused input fields, secondary wireframe button strokes, disabled touch targets, and structural cross-hatching.
- **Muted Placeholder Typography (`#a1a1aa`):** For input helper copy, tertiary captions, disabled text, and step tracker counters.
- **Secondary Typography (`#d4d4d8`):** For explanatory body paragraphs, list item secondary descriptions, and metadata labels.
- **Primary Ink / Emphasized Containers (`#f4f4f5`):** Light tone for primary call-to-action buttons, high-priority labels, page titles, and dominant navigational text against the dark backdrop.

## Typography

Typography is powered by `Inter`, prioritizing legibility, clean horizontal rhythm, and objective delivery. Because the system rejects decorative assets, visual hierarchy relies on exact sizing and font-weight contrasts:

- **Hierarchy Rules:**
  - `headline-lg` (22px/700) is reserved for primary mobile screen titles directly beneath the top bar.
  - `headline-md` (18px/600) and `headline-sm` (16px/600) demarcate sectional cards and group headings.
  - `body-md` (14px/400) acts as standard reading text and descriptive notes.
  - `label-lg` (14px/600) anchors full-width interactive buttons and navigation triggers.
  - `label-md` and `label-sm` govern input labels, step counter metrics, and component badges.
- **Wireframe Text Placeholders:** When actual copy is unspecified during drafting, use standardized geometric text-bars (`height: 10px` or `12px`, background `#3f3f46`, rounded `2px`) with varying percentage widths (e.g., 90%, 65%, 40%) to simulate text blocks without creating cognitive distractions.

## Layout & Spacing

The layout is engineered around a baseline viewport of 360px × 800px. It utilizes an absolute 4px/8px incremental grid to guarantee tight spatial alignment across vertical mobile interactions:

- **Canvas Boundaries:** Screen-edge lateral margin is fixed at 16px (`1rem`), establishing a consistent visual rail for all stacked elements.
- **Vertical Shell Structure:**
  - **Top App Bar:** Fixed height of 56px (`3.5rem`), spanning full viewport width, positioned sticky at the screen apex with 16px horizontal internal padding.
  - **Scrollable Viewport Body:** Occupies dynamic height between the 56px header and the sticky footer. Content modules stack vertically with `space-lg` (16px) or `space-xl` (24px) spacing.
  - **Sticky Action Footer:** Fixed height minimum of 64px (`4rem`), hugging the bottom edge with 16px lateral padding and a top structural divider.
- **Density Rhythm:** Micro-gaps between related controls (such as checkbox and label or icon placeholder and text) use `space-sm` (8px). Container internal paddings standardise on `space-md` (12px) or `space-lg` (16px).

## Elevation & Depth

This wireframe system does not employ elevation shadows, blurred drop-shadows, or glossy skeuomorphic depth. Depth and visual hierarchy are communicated strictly through:

- **Low-Contrast Outlines & Tonal Steps:** Containers are defined by 1px solid borders (`#52525b`). Depth between layers is conveyed by shifting the dark surface fill from `#18181b` (canvas) to `#27272a` (tier-1 container) and `#3f3f46` (nested tier-2 item).
- **Z-Index Structural Partitioning:**
  - **Level 0 (Canvas):** Base background `#18181b`.
  - **Level 1 (Cards & Groups):** Inset containers with `#27272a` fill and `#52525b` border.
  - **Level 2 (Fixed Shell Elements):** Top header bar and sticky bottom action bar use `#18181b` fill with a bottom/top 1px solid border (`#52525b`) to visually float over scrolling content without drop shadows.
  - **Level 3 (Modals / Action Sheets):** Solid `#27272a` sheets outlined with a 1.5px `#f4f4f5` border atop a semi-opaque `#000000` (50% opacity) wireframe overlay mask.

## Shapes

The shape language reflects subtle technical discipline (`roundedness: 1`). Controls and surface containers avoid overly bubbly radii, maintaining crisp schematic alignment:

- **Default UI Components (`0.25rem` / `4px`):** Used on interactive buttons, input fields, checkboxes, and informational chips.
- **Structural Cards (`0.5rem` / `8px`):** Used on bounding containers, grouped form wrappers, and interactive card modules.
- **Wireframe Placeholder Rectangles:** Media, photo, and chart placeholders use `4px` corner radii and contain interior diagonal cross-hatching (X stroke in `#52525b`) to denote skeletal content blocks.
- **Selection Controls:** Radio buttons are explicitly circular (`50%` radius, 18px diameter). Checkboxes maintain a strict `4px` radius (18px × 18px square).

## Components

### Header (Top App Bar)
- Height: 56px, background `#18181b`, border-bottom: 1px solid `#52525b`.
- Layout: Flex row, center-aligned, with 16px lateral padding.
- Left zone: 24px × 24px wireframe icon placeholder (back chevron or menu icon rendered with 1.5px `#f4f4f5` stroke).
- Center zone: Logo or view title in `headline-sm` (`#f4f4f5`).
- Right zone: Step counter badge (e.g., "STEP 2 OF 4") styled in `label-sm` (`#a1a1aa`) or text action button in `label-md`.

### Sticky Footer Action Bar
- Pinned to bottom, background `#18181b`, border-top: 1px solid `#52525b`.
- Padding: 12px 16px; min-height 64px.
- Contents: Accommodates single primary CTA (full-width) or secondary dual-button cluster (e.g., "Back" outlined, "Continue" filled).

### Buttons
- **Primary:** Full-width or inline; height 44px; background `#f4f4f5`; text `#18181b` (`label-lg`); border-radius 4px; border: none.
- **Secondary / Outlined:** Height 44px; background `#18181b`; text `#f4f4f5` (`label-lg`); border: 1px solid `#71717a`; border-radius 4px.
- **Muted / Inactive:** Height 44px; background `#3f3f46`; text `#71717a` (`label-lg`); border-radius 4px; pointer-events: none.

### Form Inputs & Text Fields
- Height 44px; background `#27272a`; border: 1px solid `#52525b`; border-radius 4px; padding: 0 12px.
- Text: Input values use `#f4f4f5` (`body-md`); placeholders use `#a1a1aa` (`body-md`).
- Focus State: Border switches to 1.5px `#f4f4f5`.
- Labels: Placed above field with 4px separation in `label-md` (`#f4f4f5`).

### Checkboxes & Radio Buttons
- Checkbox: 18px × 18px square, border: 1.5px solid `#a1a1aa`, radius 4px. Checked state: background `#f4f4f5`, checkmark `#18181b`.
- Radio: 18px × 18px circle, border: 1.5px solid `#a1a1aa`. Selected state: outer ring `#f4f4f5`, filled inner dot `#f4f4f5` (8px diameter).

### Chips & Badges
- Height: 28px; padding: 0 10px; border-radius: 4px.
- Default: Background `#27272a`, border: 1px solid `#52525b`, text `#d4d4d8` (`label-sm`).
- Selected: Background `#f4f4f5`, border: 1px solid `#f4f4f5`, text `#18181b` (`label-sm`).

### Cards & Placeholder Boxes
- **Content Card:** Background `#27272a`, border: 1px solid `#52525b`, border-radius 8px, padding 16px.
- **Image / Media Wireframe Placeholder:** Background `#27272a`, border: 1px dashed `#71717a`, border-radius 4px. Contains an inner centered diagonal cross (`X`) spanning the bounds in `#52525b` to clearly designate missing media.
- **Avatar Placeholder:** 40px × 40px circle, background `#3f3f46`, border: 1px solid `#52525b`.

### Lists & Row Dividers
- Row item height: min 48px; border-bottom: 1px solid `#3f3f46`.
- Primary row copy in `body-md` (`#f4f4f5`), secondary subtext in `body-sm` (`#a1a1aa`).
- Right-hand accessory: 16px chevron placeholder (`>`) in `#71717a`.