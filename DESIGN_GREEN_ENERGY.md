# Ecovera Design System

**Version:** 3.0 — Compact Editorial  
**Style target:** The clean, compact, modern sections preferred by the user (Core Solutions, “Because every unit of energy matters”, Industries, floating metric cards, soft lime accents, black pill CTAs).  
**Goal:** The entire website should feel tight, balanced, premium and readable — not sparse and not cramped.

---

## 1. Design Principles

1. **Compact by default** — controlled whitespace. Prefer 64–80px section gaps.
2. **Photography does the heavy lifting** — UI stays mostly neutral (near-black + soft lime).
3. **One strong accent** — soft lime used sparingly for feature cards and live metrics.
4. **Black pill primary CTAs** — high contrast, compact, 44px height.
5. **Soft elevation** — cards have light borders + subtle shadow only when floating.
6. **Clear hierarchy, short lines** — large display text, short supporting copy.
7. **Asymmetry with order** — layouts can be asymmetric, but visual rhythm stays logical.

---

## 2. Color System

### Core Tokens

| Token               | Hex       | Usage                                      |
|---------------------|-----------|--------------------------------------------|
| **primary**         | `#101310` | Primary CTAs, headings, strong text        |
| **on-primary**      | `#FFFFFF` | Text on primary buttons                    |
| **canvas**          | `#F7F8F5` | Page background (warm near-white)          |
| **surface**         | `#FFFFFF` | Cards, forms, panels                       |
| **surface-soft**    | `#FAFBF8` | Very subtle section backgrounds            |
| **ink**             | `#101310` | Primary body & headings                    |
| **slate**           | `#5C6560` | Secondary text                             |
| **muted**           | `#8B938C` | Captions, metadata (use carefully)         |
| **hairline**        | `#E4E8E2` | Card borders, dividers                     |
| **hairline-strong** | `#C5CBC4` | Input borders, outlined buttons            |

### Lime Accent Family (signature)

| Token               | Hex       | Usage                                      |
|---------------------|-----------|--------------------------------------------|
| **lime**            | `#DDF6B7` | Feature cards, metric backgrounds          |
| **lime-deep**       | `#3F6B32` | Text/icons on lime surfaces                |
| **lime-pressed**    | `#C8E69E` | Pressed state of lime buttons              |
| **lime-light**      | `#EDF9D9` | Soft badges, hover states                  |

### Supporting Greens

| Token               | Hex       | Usage                                      |
|---------------------|-----------|--------------------------------------------|
| **eco-deep**        | `#1A3320` | Dark photo overlays, dark feature cards    |
| **eco-mid**         | `#4A7A52` | Icons, secondary accents                   |

### Semantic

| Token               | Hex       | Usage                                      |
|---------------------|-----------|--------------------------------------------|
| **success**         | `#3F8A4E` | Positive metrics                           |
| **focus**           | `#1A3320` | Focus rings on light surfaces              |
| **focus-on-dark**   | `#FFFFFF` | Focus rings on dark/photo surfaces         |

**Rules**
- Lime is an **accent**, never a page background.
- Primary CTAs are always near-black (`#101310`), never lime.
- Text on lime surfaces must use `lime-deep` (`#3F6B32`) or darker.
- Never use pure `#000000`. Prefer `#101310`.

---

## 3. Typography

**Primary font:** Geist (fallback: Inter, system-ui)  
**Accent font:** Instrument Serif (italic only for short emotional phrases)

### Scale (Compact)

| Token         | Size     | Weight | Line-height | Letter-spacing | Use                              |
|---------------|----------|--------|-------------|----------------|----------------------------------|
| brand-display | 96–120px | 500    | 0.9         | -4px           | Footer / hero wordmark only      |
| hero          | 48–56px  | 500    | 1.1         | -1.5px         | Main hero headline               |
| section       | 36–40px  | 500    | 1.15        | -1px           | Section titles                   |
| heading       | 28–32px  | 500    | 1.2         | -0.5px         | Card titles, feature titles      |
| subheading    | 20–22px  | 500    | 1.3         | 0              | Smaller titles                   |
| body          | 16px     | 400    | 1.55        | 0              | Main body text                   |
| body-sm       | 14px     | 400    | 1.5         | 0              | Secondary text, descriptions     |
| caption       | 13px     | 500    | 1.4         | 0              | Labels, badges                   |
| micro         | 12px     | 500    | 1.35        | 0.3px          | Tiny metadata                    |
| button        | 14px     | 500    | 1.2         | 0              | All button labels                |
| metric        | 28–36px  | 600    | 1.1         | -0.5px         | Large numbers (1,276 MW)         |

**Emotional accent (serif)**  
Use Instrument Serif italic only for short phrases like “Greener Future” (max 3–5 words).

---

## 4. Spacing & Layout (Compact Rhythm)

### Spacing Scale
```
4 / 8 / 12 / 16 / 20 / 24 / 32 / 40 / 48 / 64 / 80
```

| Context                    | Value          |
|----------------------------|----------------|
| Card internal padding      | 20–24px        |
| Feature card padding       | 24–28px        |
| Gap between cards          | 20–24px        |
| Section vertical padding   | 64–80px        |
| Large editorial pause      | 96px (rare)    |
| Container max-width        | 1200–1280px    |
| Side gutters (desktop)     | 32–40px        |

### Grid
- 12-column
- Standard gap: 24px
- Core Solutions: text column + 3 equal cards
- Industries: 4 equal columns
- Energy section: 50/50 or 55/45 split

---

## 5. Radius & Elevation

### Radius
| Token  | Value  | Use                          |
|--------|--------|------------------------------|
| sm     | 8px    | Small controls, inputs       |
| md     | 12px   | Metric cards, small panels   |
| lg     | 16px   | Standard cards, photos       |
| xl     | 20px   | Larger feature cards         |
| full   | 9999px | Buttons, pills, badges       |

### Shadows (very restrained)
```css
/* Floating metric / plan cards */
--shadow-card: 0 8px 24px -6px rgba(16, 19, 16, 0.07);

/* Slightly stronger for important overlays */
--shadow-elevated: 0 12px 32px -8px rgba(16, 19, 16, 0.10);
```

Most cards use **only a 1px hairline border**. Shadow is reserved for floating elements (metric cards, plan cards).

---

## 6. Components (Compact Style)

### Buttons

**Primary (black pill)**
- Background: `#101310`
- Text: `#FFFFFF`
- Height: 44px
- Padding: 0 22px
- Border-radius: 9999px
- Font: 14px / 500
- Hover → `#2A2F2C`

**Lime accent button** (secondary emphasis)
- Background: `#DDF6B7`
- Text: `#101310`
- Same size and radius as primary

**Secondary / Outline**
- Transparent background
- Border: 1px solid `#C5CBC4`
- Text: `#101310`
- Height: 44px
- Border-radius: 9999px

**On-dark (white pill)** — for photo overlays and dark sections.

### Cards

**Standard white card**
- Background: `#FFFFFF`
- Border: 1px solid `#E4E8E2`
- Border-radius: 16px
- Padding: 24px
- Optional soft shadow when floating

**Lime feature card**
- Background: `#DDF6B7`
- Border-radius: 16–20px
- Text color: `#3F6B32` or `#101310`

**Dark photo card**
- Full-bleed image
- Dark gradient overlay from bottom or left
- White or light text
- Optional small dark floating panel inside

**Metric / Live data card**
- Soft lime background (`#DDF6B7` or `#EDF9D9`)
- Large number (28–36px)
- Small label underneath
- Border-radius: 12–16px
- Light shadow

**Plan card**
- White background + soft shadow
- Clear hierarchy: Title → description → price → arrow

### Navigation
- Height: 64–72px
- Logo left
- Links in the middle
- Right side: Primary pill “Get Started” + outlined “Contact”
- On hero: light text + subtle scrim
- Sticky version: white/off-white + hairline bottom

### Badges & Labels
- Soft lime or dark pills
- 12–13px, medium weight
- Padding: 4px 10px
- Fully rounded

---

## 7. Section Patterns (Exact Compact Style)

### 1. Core Solutions
```
[ Large editorial sentence with inline green badge ]
[ “Core Solutions” label + short description ]     [ Photo card ] [ Lime card ] [ Photo card ]
                                                   [ Black “Know more” button under text ]
```
- Cards equal height
- One lime card as accent
- Compact gap 20–24px

### 2. “Because every unit of energy matters”
```
Left: text + black button + “Explore more”
Right:
  - Large photo card with overlay title + tags (Solar / Wind / Hydro)
  - Floating soft-lime metric card (Live Energy Output)
Below:
  - Left: photo with dark floating panel
  - Right: two white plan cards + another metric
```
This is the key compact-premium pattern.

### 3. Industries
```
Title left + short description right + arrow controls
[ 4 equal photo cards with rounded corners ]
Title + one-line description under each photo
```
- No heavy card borders
- Consistent image ratio
- Compact spacing

### 4. Process / How it works
- Large faint background image (turbine)
- 4–5 process cards around it
- One card can be lime (active state)
- Mobile → simple vertical stack

### 5. Impact / Metrics
- Large photo background
- 1–2 floating white or lime metric cards
- Never cover the main subject of the photo

### 6. Final CTA
- Full-width photo with strong bottom scrim
- Centered short headline + email input + black or white pill button

### 7. Footer
- Near-black (`#101310` or `#050706`)
- 3–4 link columns
- Large white wordmark at the bottom
- Compact padding

---

## 8. Photography Rules

- Prefer real renewable infrastructure + landscape
- Soft natural light, slightly desaturated greens
- Always add local gradient/scrim when placing light text on photo
- Floating cards must never hide the main subject (turbine, building, horizon)
- Consistent border-radius 16px on solution/industry images

---

## 9. Interaction

- Hover on cards: slight lift (`translateY(-3px)`) + stronger shadow
- Buttons: 150–200ms ease
- Focus ring: 2px solid `#1A3320` with 2px offset
- Respect `prefers-reduced-motion`

---

## 10. Responsive Behavior

| Breakpoint     | Behavior                                      |
|----------------|-----------------------------------------------|
| < 768px        | Single column, hero 36–40px, cards stack      |
| 768–1023px     | 2-column grids where sensible                 |
| ≥ 1024px       | Full layouts as designed                      |
| ≥ 1280px       | Max container 1280px                          |

Touch targets remain 44px minimum.

---

## 11. Do’s and Don’ts

### Do
- Keep primary CTAs black pills
- Use soft lime only for selected feature cards and live metrics
- Maintain compact section spacing (64–80px)
- Use 16px radius as the default card language
- Give floating metric cards a soft shadow
- Keep text short and hierarchical
- Add scrims on photography whenever text sits on top

### Don’t
- Don’t make the whole site green
- Don’t use lime as the main CTA color
- Don’t use large 28–32px radius everywhere
- Don’t apply heavy shadows on every card
- Don’t create large empty vertical gaps
- Don’t put low-contrast text on lime or photo backgrounds
- Don’t use pure black or pure white for large text areas

---

## 12. CSS Starter Tokens

```css
:root {
  --primary: #101310;
  --on-primary: #FFFFFF;
  --canvas: #F7F8F5;
  --surface: #FFFFFF;
  --ink: #101310;
  --slate: #5C6560;
  --muted: #8B938C;
  --hairline: #E4E8E2;
  --hairline-strong: #C5CBC4;

  --lime: #DDF6B7;
  --lime-deep: #3F6B32;
  --lime-light: #EDF9D9;
  --eco-deep: #1A3320;

  --radius-card: 16px;
  --radius-button: 9999px;
  --shadow-card: 0 8px 24px -6px rgba(16, 19, 16, 0.07);

  --font-sans: 'Geist', 'Inter', system-ui, sans-serif;
  --font-serif: 'Instrument Serif', Georgia, serif;
}
```

---

**End of Design System v3.0 — Compact Editorial**

This version is tuned specifically to the compact, clean, modern style shown in the preferred screenshots.  
All major sections of the site should now follow the same tight rhythm, card language, black-pill CTAs and restrained lime accent.
