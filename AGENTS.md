# Правила проекта

## Совместимость с MySQL/MariaDB

Проект должен работать с текущим сервером, где максимальная длина индекса ограничена 1000 байт, а строки используют `utf8mb4`.

- Не создавать индексируемые строковые поля длиннее 191 символа.
- Для уникальных и обычных индексов использовать, например, `$table->string('email', 191)->unique()`.
- Глобальное значение по умолчанию настроено в `AppServiceProvider` через `Schema::defaultStringLength(191)`.
- После добавления или изменения миграций проверять их на чистой тестовой базе.
- Не запускать `php artisan migrate:fresh` на production: команда удаляет все таблицы и данные.

## Mobile layout integrity

- Never place a fixed mobile navigation inside an ancestor that has `transform`, `filter`, `backdrop-filter`, `perspective`, or `contain`; these properties can change the fixed element's containing block and shift or clip the menu.
- The mobile navigation must be viewport-bound: use `position: fixed`, `inset: 0`, `width: 100vw`, `height: 100dvh`, and `box-sizing: border-box`.
- Keep the page and visual sections constrained to the viewport (`html`, `body`, `main`, hero, and CTA), and verify on mobile that `document.documentElement.scrollWidth === document.documentElement.clientWidth` so no horizontal overflow or right-side gap is introduced.
- Before finishing a responsive change, test both closed and open menu states at a narrow viewport and check the computed geometry of the header, hero, and navigation.

## CSS scope and component isolation

- Do not add broad global styles for a class that is already used by another page or component. Before creating a selector, search the repository for the class name with `rg`.
- Page-specific styles must be scoped under a page namespace such as `.page-section`, `.page-shell`, or a dedicated component prefix. Prefer unique names like `.page-news-card` over reusing generic names such as `.news-card`.
- When adding or changing a component used on an internal page, verify that the same selector does not change the home page. Check both the rendered markup and CSS cascade/order.
- `public/css/site.css` and `public/js/site.js` are the only runtime frontend sources. Do not duplicate them in `resources`; the project has no required npm build step.
- Before finishing a visual change, inspect the home page and the changed page, run `git diff --check`, and complete the visual gate defined in `DESIGN_ACCEPTANCE.md`.

## Internal page first screen

- Do not add a photo hero or `.page-hero` to internal pages (`/about/*`, `/business`, `/news`, `/stories`, `/media`, `/partners`, `/contacts`); the home page hero is the only exception.
- The first visible block on internal pages must begin with content. Use a two-column header: title and marker on the left, a short description on the right; `/news` and `/stories` may use that right column for their controls.
- Media navigation is page-based: `Фото`, `Видео` and `Каталоги` must use separate routes (`/media/photos`, `/media/videos`, `/media/catalogues`). Do not replace them with hash anchors on one combined page.
- Media subpages begin with a quiet breadcrumb-style three-column switcher; do not add a second introductory hero or a right-side explanatory block above it. Keep the three links in one row on mobile and use the dark-green active state.
- Keep a deliberate visual gap between the floating menu and the first content: target approximately 64px on desktop and 48px on mobile. Do not let the first marker or heading touch the menu.
- If the internal first-screen structure changes, verify the desktop and mobile layouts and confirm that the home page markup and hero remain unchanged.

## Multilingual typography

- Use one Cyrillic-capable display sans-serif for RU, RO and EN so the same component does not change font metrics between languages; the current project font is `Inter Tight`.
- Headings must use `text-wrap: balance` and must not depend on manual `<br>` tags or a fixed one-line height.
- Russian headings may use a small letter-spacing adjustment, but do not reduce body-text size or readability to force a translation into one line.

## Card alignment verification

- Every repeated card must be checked as a complete unit: image, badge, metadata, title, description, deadline and CTA must use the same horizontal content edge.
- Do not combine parent padding with inherited child margins until the resulting geometry has been checked; explicitly reset margins when a nested footer or action row must align with the card body.
- Before finishing a card change, compare at least the first, middle and last cards at desktop and mobile widths. Verify equal image heights, equal card widths, equal content offsets and aligned bottom actions.

## Visual completion gate

- Do not treat HTTP 200, compiled Blade, or passing PHP tests as proof that a design is correct; these checks do not validate composition, spacing, overflow or readability.
- Before reporting a visual task complete, run `powershell -ExecutionPolicy Bypass -File scripts/visual-audit.ps1 -Full` and inspect representative screenshots listed in `DESIGN_ACCEPTANCE.md`.
- Check RU, RO and EN at desktop and mobile widths. For navigation changes, check both closed and open mobile states.
- If the audit reports a geometry failure, fix it before continuing with decorative changes or animation.
