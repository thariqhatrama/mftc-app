---
name: Ethos Modern
colors:
  surface: '#f9f9ff'
  surface-dim: '#d3daea'
  surface-bright: '#f9f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f3ff'
  surface-container: '#e7eefe'
  surface-container-high: '#e2e8f8'
  surface-container-highest: '#dce2f3'
  on-surface: '#151c27'
  on-surface-variant: '#3f4944'
  inverse-surface: '#2a313d'
  inverse-on-surface: '#ebf1ff'
  outline: '#6f7973'
  outline-variant: '#bec9c2'
  surface-tint: '#1b6b51'
  primary: '#004532'
  on-primary: '#ffffff'
  primary-container: '#065f46'
  on-primary-container: '#8bd6b7'
  inverse-primary: '#8bd6b6'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#333f39'
  on-tertiary: '#ffffff'
  tertiary-container: '#4a564f'
  on-tertiary-container: '#becac2'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a6f2d1'
  primary-fixed-dim: '#8bd6b6'
  on-primary-fixed: '#002116'
  on-primary-fixed-variant: '#00513b'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#d9e6dd'
  tertiary-fixed-dim: '#bdcac1'
  on-tertiary-fixed: '#131e19'
  on-tertiary-fixed-variant: '#3e4943'
  background: '#f9f9ff'
  on-background: '#151c27'
  surface-variant: '#dce2f3'
typography:
  h1:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  h2:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: '0'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: '0'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  button:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1'
    letterSpacing: 0.01em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 0.5rem
  sm: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  gutter: 1.5rem
  container-max: 1280px
---

## Brand & Style

The design system is rooted in the concepts of **integrity, clarity, and architectural precision**. Built for a certification platform, the aesthetic must bridge the gap between regulatory authority and modern accessibility. The brand personality is professional and steadfast, designed to evoke a sense of "certified excellence" and global trust within the Muslim-friendly ecosystem.

The chosen style is **Corporate / Modern**, leaning heavily on **Minimalism** for information density management. It utilizes generous whitespace to reduce cognitive load during complex certification workflows. Subtle geometric patterns inspired by traditional motifs may be used as low-opacity watermarks, but the primary interface remains focused on high-contrast readability and functional efficiency.

## Colors

This design system uses a focused emerald palette to symbolize growth and ethical standards. 
- **Primary (#065f46):** Deep Emerald. Used for high-level branding, primary actions, and "Verified" states. It provides the "anchor" of trust.
- **Secondary (#10b981):** Bright Emerald. Used for progress indicators, success states, and interactive accents that require attention without the weight of the primary color.
- **Tertiary (#f0fdf4):** Mint Tint. Used for background fills in cards or status badges to provide soft contrast against the white background.
- **Neutral:** A scale of slate grays is used for typography and structural borders to maintain a professional, unbiased atmosphere.

## Typography

The design system exclusively utilizes **Inter** to leverage its exceptional legibility and neutral, systematic character. The typographic hierarchy is strictly controlled to differentiate between "Instructional" text and "Data" text. 

Headlines use tighter tracking and heavier weights to project authority. Body text maintains a generous line height (1.5 - 1.6) to ensure that dense certification requirements are easy to parse. Small labels and metadata utilize an uppercase style with increased letter spacing to provide a clean, "tabular" look suitable for a professional platform.

## Layout & Spacing

The design system employs a **Fixed Grid** model for desktop, centered within a 1280px container. It follows a 12-column structure with 24px (1.5rem) gutters. 

Rhythm is maintained using a 4px baseline. Components like data tables and multi-step wizards should utilize "MD" (24px) padding internally to maintain a spacious, breathable feel. For responsive transitions, the grid shifts to an 8-column layout for tablets and a 4-column fluid layout for smaller viewports, though the primary focus remains a structured desktop dashboard.

## Elevation & Depth

To maintain a "clean and white" aesthetic, the design system avoids heavy shadows. Instead, it uses **Tonal Layers** and **Low-contrast Outlines**:

- **Level 0 (Base):** Pure white (#ffffff) or ultra-light gray (#f9fafb).
- **Level 1 (Cards/Tables):** White surface with a 1px border in a soft neutral (#e5e7eb).
- **Level 2 (Dropdowns/Modals):** White surface with a very subtle ambient shadow (0px 4px 20px rgba(0, 0, 0, 0.05)) to suggest a "lift" without appearing heavy.
- **Interactions:** Hover states on interactive elements should use a slight background tint change rather than a shadow increase, maintaining the flat, professional profile of the system.

## Shapes

The design system uses a **Soft (0.25rem)** corner radius as the standard. This choice balances the rigidity of a corporate tool with the friendliness required for a community-centric platform. 

Standard components (inputs, buttons, cards) use 4px (0.25rem) rounding. For larger containers like "Wizard Steps" or "Success Modals," a 12px (0.75rem) radius is used to provide a slightly more modern, approachable feel. Status badges and tags utilize a full pill-shape (999px) to distinguish them clearly from interactive buttons.

## Components

### Progress Bars & Multi-Step Wizards
Progress bars should use the Secondary Emerald (#10b981) for the fill, with a Tertiary (#f0fdf4) background track. Wizards are displayed as a horizontal track of numbered circles; completed steps show a checkmark in Primary Emerald, while the active step has a 2px outer ring.

### Status Badges
Badges use a "Tint-on-Tint" approach. 
- **Active/Certified:** Dark green text on a light green background.
- **Pending:** Amber text on a light yellow background.
- **Action Required:** Red text on a soft pink background.
All badges are pill-shaped with uppercase label typography.

### Data Tables
Tables are the heart of the system. They feature a "no-border" internal style, using only horizontal dividers in light gray. The header row is sticky with a light gray background (#f3f4f6) and bold, small-caps typography. Rows should have a subtle hover effect (Secondary Emerald at 5% opacity).

### Input Fields & Buttons
- **Primary Button:** Solid Primary Emerald (#065f46) with white text.
- **Secondary Button:** Outlined Primary Emerald with 1px border.
- **Inputs:** 1px neutral border, turning into a 2px Primary Emerald border on focus. No inner shadows.

### Cards
Cards are white with a 1px border. For certification modules, cards may include a left-hand border accent in Primary Emerald (4px width) to denote importance or "Certified" status.