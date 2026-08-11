# Green Energy Hub — visual acceptance contract

This file records the current accepted interface rules. It takes precedence over obsolete screenshots and superseded design iterations. `DESIGN_GREEN_ENERGY.md` remains the design-system reference for tokens and component language.

## Runtime architecture

- The site requires no npm command and no frontend build step.
- `public/css/site.css` is the only runtime and editable stylesheet.
- `public/js/site.js` is the only runtime and editable script.
- `resources/css/app.css` and `resources/js/app.js` are intentionally empty compatibility entries. Do not copy runtime code into them.
- Home and internal cards must not share component selectors:
  - home: `.news-card`, `.opportunity-card`;
  - internal pages: `.page-news-card`, `.page-opportunity-card`.

## Header and first-screen geometry

- Header and content geometry is controlled by CSS tokens:
  - `--site-header-top`;
  - `--site-header-height`;
  - `--site-header-content-gap`;
  - `--internal-page-start`.
- Internal content must begin approximately 64px below the header on desktop and 48px below it on mobile.
- The home hero is full-bleed. Its background must never inherit the content container's outer margins.
- Internal pages do not use a photo hero. Their first block begins with content.
- Internal first blocks use a left title and a right summary. News and opportunities may use the right column for controls.

## Responsive invariants

- No horizontal document overflow is allowed at any tested viewport.
- The mobile navigation is bound to the viewport: `position: fixed`, `inset: 0`, `width: 100vw`, `height: 100dvh`.
- Opening the mobile navigation locks page scrolling; the menu itself remains vertically scrollable.
- The close control remains visible at the top of the viewport.
- RU, RO and EN must be checked because typography and line wrapping differ.

## Repeated-card invariants

- Cards in one grid have equal widths and equal image heights.
- Image, badge, title, description, metadata, deadline and CTA share one horizontal content edge.
- Home-card changes must be checked on the home page. Internal-card changes must be checked on `/news` and `/stories`.
- Never modify a generic card selector before searching all of its usages with `rg`.

## Required verification matrix

Before declaring a visual change complete, run:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/visual-audit.ps1 -Full
```

The full audit covers every public route at:

- 1440×900 and 390×844;
- RU, RO and EN;
- closed navigation on every page;
- open mobile navigation on the home page and one internal page.

The script writes screenshots and `summary.json` to a timestamped directory under `storage/app/visual-audit`.

Automated geometry checks are necessary but not sufficient. Inspect at least these screenshots manually after every substantial design change:

- home desktop RU;
- home mobile RU;
- changed page desktop RU;
- changed page mobile RU;
- mobile menu open;
- the longest RO or EN state affected by the change.

## Completion gate

A visual task is complete only when all conditions hold:

1. Blade templates compile and feature tests pass.
2. `node --check public/js/site.js` passes.
3. `git diff --check` passes.
4. The visual audit has no failed cases.
5. Representative screenshots have been visually inspected.
6. The home page was checked whenever shared layout, typography, navigation or cards changed.
