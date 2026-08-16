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
- `public/css/site.css` and `public/js/site.js` are the public runtime frontend sources. Filament admin runtime assets belong in `public/css/filament-admin.css` and `public/js/filament-admin.js`; do not duplicate either runtime source in `resources`.
- Before finishing a visual change, inspect the home page and the changed page, run `git diff --check`, and complete the visual gate defined in `DESIGN_ACCEPTANCE.md`.

## Internal page first screen

- Do not add a photo hero or `.page-hero` to internal pages (`/about/*`, `/business`, `/news`, `/stories`, `/media`, `/partners`, `/contacts`); the home page hero is the only exception.
- News and opportunity detail pages are text-first: do not render a cover image on `/news/{slug}` or `/stories/{slug}`. Cover images remain allowed in the listing cards.
- The first visible block on internal pages must begin with content. Use a two-column header: title and marker on the left, a short description on the right; `/news` and `/stories` may use that right column for their controls.
- Media navigation is page-based: `Фото`, `Видео` and `Каталоги` must use separate routes (`/media/photos`, `/media/videos`, `/media/catalogues`). Do not replace them with hash anchors on one combined page.
- Media subpages begin with a quiet breadcrumb-style three-column switcher; do not add a second introductory hero or a right-side explanatory block above it. Keep the three links in one row on mobile and use the dark-green active state.
- Keep a deliberate visual gap between the floating menu and the first content: target approximately 64px on desktop and 48px on mobile. Do not let the first marker or heading touch the menu.
- News and opportunity detail pages must keep the cover, text blocks, single images, galleries and embeds on the same `.container` edges as the site header; do not assign separate arbitrary `max-width` values to article content blocks.
- If the internal first-screen structure changes, verify the desktop and mobile layouts and confirm that the home page markup and hero remain unchanged.

## Multilingual typography

- Use one Cyrillic-capable display sans-serif for RU, RO and EN so the same component does not change font metrics between languages; the current project font is `Inter Tight`.
- Headings must use `text-wrap: balance` and must not depend on manual `<br>` tags or a fixed one-line height.
- Russian headings may use a small letter-spacing adjustment, but do not reduce body-text size or readability to force a translation into one line.

## Color usage

- Do not use green as a large-area fill for content sections or page backgrounds. Keep major surfaces neutral or photographic.
- Use green only as a restrained accent: buttons, markers, lines, dots, badges, borders, small cards and active states.
- Text on light-green surfaces must be black or neutral-black, never green. This applies to headings, body copy, metadata, links and CTA labels.
- When changing a section background, inspect the whole page for the same large-surface pattern and preserve the neutral/accent balance across desktop and mobile.

## Card alignment verification

- Every repeated card must be checked as a complete unit: image, badge, metadata, title, description, deadline and CTA must use the same horizontal content edge.
- Do not combine parent padding with inherited child margins until the resulting geometry has been checked; explicitly reset margins when a nested footer or action row must align with the card body.
- Before finishing a card change, compare at least the first, middle and last cards at desktop and mobile widths. Verify equal image heights, equal card widths, equal content offsets and aligned bottom actions.

## Visual completion gate

- Do not treat HTTP 200, compiled Blade, or passing PHP tests as proof that a design is correct; these checks do not validate composition, spacing, overflow or readability.
- Before reporting a visual task complete, run `powershell -ExecutionPolicy Bypass -File scripts/visual-audit.ps1 -Full` and inspect representative screenshots listed in `DESIGN_ACCEPTANCE.md`.
- Check RU, RO and EN at desktop and mobile widths. For navigation changes, check both closed and open mobile states.
- If the audit reports a geometry failure, fix it before continuing with decorative changes or animation.

## Editorial admin workflow

- News and opportunity editors must open on the Russian tab; Romanian and English content stay in separate tabs and are translated from Russian inside the corresponding tab.
- Drafts may be saved with partial content. Validate the Russian title, short description and cover only when the editor explicitly publishes or schedules the material.
- Field-level validation feedback must be state-aware: when an editor changes a value to a valid state, clear the stale validation error and red styling for that field immediately.
- Publication mode and date are coupled: drafts have no publication date, “publish now” assigns the current time, and only scheduled publication exposes a date/time field.
- Keep slug, author and SEO fields inside the dedicated `Дополнительные настройки` tab within the main tabs; do not wrap the tabs in another visual section or put technical fields before the editorial content.
    - Editorial forms may use at most two visible container levels: the language tabs and the builder block. Do not add an outer `Section` around the tabs or another card around an individual editor block.
    - The editorial language tabs must use a non-contained presentation (`Tabs::contained(false)`); the tab panel must not add a large card around the editor.
    - The editorial language-tab navigation must span the form width and align to the left; the active language must be visually unambiguous using a light-green state with black text and a dark-green accent.
    - Inside an editorial Builder block, keep the block header and its controls as the only visible card; the nested RichEditor must be borderless and must not create a second card.
- The editor must provide a visible save-draft action, preview action and publication menu at the top. Do not reintroduce the default “Create/Create another” footer as the primary workflow.
- Keep autosave limited to draft state and preserve translation metadata so a changed Russian source marks existing translations as stale.
- Translation tab status badges are mutually exclusive: show only a checkmark for a complete current translation, or only a warning for missing/stale content. Never combine both symbols.
- RichEditor values may be structured Tiptap arrays, not only strings. Before hashing or comparing editorial text, normalize them through `RichText::toText()`; never cast a RichEditor state directly to `(string)`. When sending content for AI translation, use `RichText::toHtml()` instead: preserve separate `<p>` elements, inline formatting and line breaks so translated content is not flattened into one paragraph.
- The editorial content builder must keep a visible gap between independent blocks, and after adding or cloning a block the interface must automatically scroll the new block into view. Do not rely on the user to find a block below the current viewport.

## Regression test gate

- Before handing off any change, run `composer test` and require every test to pass.
- For changes to News, Opportunities, Tags, Filament forms, translations, OpenRouter settings, or image processing, extend `tests/Feature/ContentManagementRegressionTest.php` with a regression test in the same change.
- Verify both behavior and integration: public visibility and ordering, Russian fallback, generated slugs, Filament navigation/forms, encrypted settings, OpenRouter response validation, preservation of image blocks, and AVIF dimensions.
- Do not use live network calls in tests. Mock OpenRouter with `Http::fake()` and use `Storage::fake()` for uploaded images.
- A visual change is not complete until both `composer test` and `powershell -ExecutionPolicy Bypass -File scripts/visual-audit.ps1 -Full` pass; `git diff --check` must also pass.

## Public uploaded images

- Files saved to the `public` filesystem disk are served through the `public/storage` link. After every fresh install or deployment, run `php artisan storage:link` and verify that a real `/storage/...` image URL returns HTTP 200.
- Filament table `ImageColumn` components that display uploaded files must explicitly use `->disk('public')`; the application default disk is `local`, so omitting the disk produces broken thumbnails even when the file exists.
- Do not commit `public/storage`; it is a generated link and is already ignored by Git.

## Filament image preview contract

- Treat every uploader as a fixed, named UI variant. The FilePond ratio and the server-side crop ratio must be the same: editorial covers use the public card ratio `11:5` (`2.2:1`), standalone article images use `16:9`, image-plus-text and album photos/covers use `4:3`, galleries 2/3 use `4:3`, and gallery 4 uses `3:4`.
- Configure image geometry through the FileUpload/FilePond API in the shared `FilamentImageUpload` helper. Do not calculate preview geometry from FilePond canvas/DOM dimensions and do not alter a FilePond root with MutationObserver, CSS variables or inline width/height after rendering.
- Every persisted upload must use the shared relative `/storage/...` URL resolver. Verify new upload, saved draft, reopened edit form and replacement/removal of a file for every supported upload variant.
- When changing image uploads, add a regression assertion for the relevant FileUpload configuration and run the authenticated FilePond scenario in addition to public-page visual audit. Check all three states: empty slot (centred placeholder), processing upload (progress may be visible), and completed upload (only the image plus edit/remove controls; no filename, success banner or status overlay).
- For uploads nested inside `Builder -> Repeater`, set `fetchFileInformation(false)` in the shared upload helper. The default disk-existence check can remove a temporary Livewire upload during the next hydration before the editor is saved, causing the gallery path to be persisted as `null`.
- For fixed-ratio gallery cells, set all three native FilePond options together in `FilamentImageUpload`: `panelLayout('integrated')`, `panelAspectRatio()` and `itemPanelAspectRatio()`. The `compact` list-layout is forbidden for gallery cells because it leaves a separate file/status area above the image. A ratio alone controls the empty panel but does not guarantee that an uploaded item fills it.
- The uploaded item must fill the same fixed gallery cell as the empty drop target: keep the FilePond list, item, wrapper and image clip at `width: 100%; height: 100%`, and use `object-fit: cover` for the bitmap/vector. Never allow the native image ratio to create black pillarbox/letterbox bands or a second empty area inside a gallery cell.

### Proven FilePond patterns

- The working gallery implementation is the reference for all future image
  upload changes. Keep the two variants separate:
  - `gallery_2` and `gallery_3`: fixed `4:3` landscape cells;
  - `gallery_4`: fixed `3:4` portrait cells;
  - editorial cover: fixed `11:5` card ratio;
  - standalone article image: fixed `16:9` ratio;
  - image-plus-text and album images: fixed `4:3` ratio.
- Build fixed galleries through one shared `Repeater` schema. Create all slots
  immediately and keep their count fixed; do not use a compact FilePond list:

  ```php
  Repeater::make('images')
      ->schema([self::imageUpload('path', $previewClass)->hiddenLabel()])
      ->defaultItems($count)
      ->minItems($count)
      ->maxItems($count)
      ->addable(false)
      ->deletable(false)
      ->reorderable(false);
  ```

- The shared helper must configure all native geometry options together for a
  fixed gallery cell:

  ```php
  return static::configure($upload)
      ->panelLayout('integrated')
      ->panelAspectRatio($ratio)
      ->itemPanelAspectRatio($ratio)
      ->imageCropAspectRatio($ratio);
  ```

- Do not derive preview geometry from a temporary browser image, FilePond
  canvas or DOM dimensions. Do not change FilePond ratios after hydration and
  do not use `MutationObserver`, CSS variables or inline root dimensions to
  force a preview. Those approaches caused unstable empty states and stale
  previews. Configure the fixed ratio in `FilamentImageUpload` and pass the
  same ratio to `ImageProcessor::store()` for the server-side crop.
- A complete fixed gallery cell must use the same geometry for empty and
  uploaded states. The required CSS relationship is:

  ```css
  .gallery-upload .filepond--drop-label {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
  }

  .gallery-upload .filepond--list,
  .gallery-upload .filepond--item,
  .gallery-upload .filepond--file-wrapper,
  .gallery-upload .filepond--image-preview-wrapper,
  .gallery-upload .filepond--image-clip {
      width: 100%;
      height: 100%;
  }

  .gallery-upload .filepond--image-bitmap,
  .gallery-upload .filepond--image-vector {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }
  ```

- For an empty cover/dropzone, center the label across the whole panel and
  keep FilePond's native label/file-picker behavior. Do not add a custom
  click bridge or manually call `pond.browse()`: custom bridges were part of
  the removed adaptive implementation and could conflict with FilePond and
  Livewire event handling.

- Hide filename, success/status and preview-overlay layers after processing is
  complete in gallery cells. The completed state should contain only the
  image and edit/remove controls; an upload-progress overlay is allowed only
  while processing.
- For every upload change, verify all states and persistence: empty slot,
  processing upload, completed upload, saved draft, reopened edit form,
  replacement, and removal. For `Builder -> Repeater` uploads, the regression
  test must assert `fetchFileInformation(false)`, the fixed ratio, integrated
  layout, and restored paths after reopening.

### Canonical image pipeline (source of truth)

This is the required image architecture for both a new Laravel project and
any extension of this project. The successful `gallery_2/3/4` implementation
is the reference implementation, but the same lifecycle applies to every
image field. Do not create a second upload implementation for a new resource.

#### 1. Define one named ratio per public component

The public frame and the Filament frame must use the same ratio. The server
must crop to that ratio before writing the final AVIF file.

| Component | Public ratio | Current constant | Storage directory |
| --- | --- | --- | --- |
| News/opportunity cover card | `2.2:1` | `CARD_RATIO = '11:5'` | `uploads/covers` |
| Standalone article image | `16:9` | `ARTICLE_RATIO = '16:9'` | `content` |
| Image + text | `4:3` | `LANDSCAPE_RATIO = '4:3'` | `content` |
| Photo-album cover | `4:3` | `LANDSCAPE_RATIO = '4:3'` | `uploads/albums` |
| Photo-album photo | `4:3` | `LANDSCAPE_RATIO = '4:3'` | `uploads/albums` |
| Gallery with 2 or 3 photos | `4:3` | `LANDSCAPE_RATIO = '4:3'` | `content` |
| Gallery with 4 photos | `3:4` | `PORTRAIT_RATIO = '3:4'` | `content` |

Ratios are not hints. Changing a public `aspect-ratio` requires changing the
named helper constant, FilePond configuration, server crop argument, CSS
selectors if necessary, and the regression test in the same change.

#### 2. Configure every FileUpload through the shared helper

The only supported entry point is:

```php
$upload = FileUpload::make('path')
    ->image()
    ->imageEditor()
    ->previewable()
    ->disk('public')
    ->visibility('public');

$upload = FilamentImageUpload::fixedGrid(
    $upload,
    FilamentImageUpload::LANDSCAPE_RATIO,
);
```

`FilamentImageUpload::configure()` must remain responsible for:

- `fetchFileInformation(false)`, especially for `Builder -> Repeater`;
- loading existing files through a relative `/storage/...` URL;
- returning the stored file name, size and MIME type without relying on the
  current domain or `APP_URL`.

`fixedGrid()` must always set all native geometry options together:

```php
->panelLayout('integrated')
->panelAspectRatio($ratio)
->itemPanelAspectRatio($ratio)
->imageCropAspectRatio($ratio)
```

Never use the compact FilePond list layout for an image grid. Never derive a
ratio from the browser image, canvas, FilePond DOM, a `MutationObserver`, CSS
variables or inline dimensions. There is no adaptive/source-ratio uploader in
the project anymore.

#### 3. Keep the empty and completed states geometrically identical

The dropzone must already have the final ratio before a file is selected. The
placeholder text is centered with the drop label. After upload, the list,
item, file wrapper, preview wrapper and image clip all fill that same cell:

```css
.upload-variant .filepond--drop-label {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-variant .filepond--list,
.upload-variant .filepond--item,
.upload-variant .filepond--file-wrapper,
.upload-variant .filepond--image-preview-wrapper,
.upload-variant .filepond--image-clip,
.upload-variant .filepond--image-bitmap,
.upload-variant .filepond--image-vector {
    width: 100%;
    height: 100%;
}

.upload-variant .filepond--image-bitmap,
.upload-variant .filepond--image-vector {
    object-fit: cover;
    object-position: center;
}
```

`object-fit: contain`, black letterbox/pillarbox fields, a second status
panel above the image, and a fixed `min-height` that overrides the ratio are
forbidden. Completed items show the image and edit/remove controls only; file
name, success banner and status overlay are hidden after processing.

Editorial and album-cover fields are intentionally compact in the admin form:
they are `33.3333%` wide and left-aligned on desktop, and `100%` wide below
`768px`. This is only the form field width; it must not change the editorial
cover's `11:5` ratio, album-cover `4:3` ratio, public card width, or any
gallery cell.

#### 4. Save through the one server-side processor

Every `saveUploadedFileUsing` callback must call `ImageProcessor::store()` and
pass the exact same ratio used by FilePond:

```php
->saveUploadedFileUsing(
    fn (UploadedFile $file): string => app(ImageProcessor::class)->store(
        $file,
        'content',
        FilamentImageUpload::LANDSCAPE_RATIO,
    ),
)
```

`ImageProcessor::store()` performs the operations in this order:

1. Read the temporary upload with Intervention Image.
2. Center-crop it to the requested ratio. Do not stretch and do not add a
   background/letterbox.
3. Scale down to the configured maximum dimension without upscaling.
4. Encode as AVIF using the configured quality.
5. Save to the public disk under a UUID-based `.avif` path.

The settings page controls `images.max_dimension` and `images.avif_quality`.
The processor still clamps values defensively (`max dimension >= 320`, quality
between `20` and `100`). A new uploader must never write the original JPEG,
PNG or a full-resolution temporary file as the final content path.

#### 5. Preserve Livewire/FilePond hydration

The custom `getUploadedFileUsing()` callback is required because temporary
Livewire uploads can disappear during hydration when FilePond performs a
default disk-existence lookup. It must resolve the persisted relative path
from the configured public disk and return a URL beginning with `/storage/`.

Do not add `wire:ignore` around a Filament FileUpload to hide hydration
problems. Do not mutate the FilePond root after render. If an image vanishes
after reopening an edit page, inspect the returned file descriptor, storage
disk, relative URL, `storage:link`, and Livewire state before changing CSS.

#### 6. Gallery blocks must remain fixed-slot repeaters

For galleries, create the exact number of slots immediately and make the
count non-editable:

```php
Repeater::make('images')
    ->schema([self::imageUpload('path', $previewClass)->hiddenLabel()])
    ->defaultItems($count)
    ->minItems($count)
    ->maxItems($count)
    ->addable(false)
    ->deletable(false)
    ->reorderable(false);
```

`gallery_2` and `gallery_3` use landscape cells; `gallery_4` uses portrait
cells and is two columns on mobile. Do not replace these with a multiple-file
upload, a compact list, or a new custom preview component.

#### 7. Required verification for any image change

Before merging an image-related change, verify all variants at these states:

- empty dropzone: correct ratio, centered text, native file picker;
- processing: progress may be visible, but the cell must not gain a second
  dark rectangle;
- completed upload: one cropped image fills the cell;
- saved draft reopened in edit: preview is present and uses `/storage/...`;
- replacement and removal: state and stored path update correctly;
- public page: image ratio and crop match the admin frame.

Tests must use `Storage::fake('public')`, generated images with deliberately
different source ratios, and assertions for both FileUpload options and final
AVIF dimensions. For OpenRouter or other unrelated integrations, keep tests
offline and mock the network. The minimum regression coverage belongs in
`tests/Feature/ContentManagementRegressionTest.php`.

For a fresh Laravel project, install the same image-processing package,
create the shared helper and processor before creating resource forms, set up
the public disk and `php artisan storage:link`, then add the ratio/configuration
tests before adding the first new uploader. For this project, extend the
existing helper and processor instead of copying either implementation.

## Agent execution protocol

These rules exist to prevent regressions caused by acting on an incomplete interpretation of a screenshot or the latest message.

### Before editing

- Read the latest user request as a change contract. Write down the requested result, the exact routes/components in scope, and explicit non-goals such as `главную не трогать`, `анимации не менять` or `только исправить отступ`.
- Treat the latest explicit instruction as authoritative only within its stated scope. Do not revive a previously rejected design, text, color, animation or layout from an earlier message.
- If the request changes a design direction, first inspect the current rendered markup and the current design/acceptance documents. Do not combine old and new design variants.
- Search the repository for the target route, Blade component, selector and stylesheet with `rg` before editing. Trace the full path: route → controller → view/component → runtime CSS/JS. Never assume that a similarly named file is the active source.
- Inspect `git status --short` and the relevant diff before touching overlapping files. Existing user changes are protected and must not be silently reformatted, reverted or replaced.
- Ask one focused question when two plausible implementations would materially change the result. If the answer is already discoverable from the repository, inspect it instead of asking the user to repeat it.

### While editing

- Prefer the smallest change that satisfies the contract. A bug fix must not become a redesign, and a page-specific request must not alter shared selectors or the home page unless that is explicitly requested.
- Do not add decorative blocks, animations, backgrounds, copy or containers merely to make a page feel fuller. Every new element must have a stated UX purpose and a corresponding acceptance check.
- For layout work, measure the geometry that caused the complaint: content edges, header-to-content gap, card/image heights, line wrapping, viewport width and overflow. Do not use visual intuition as a substitute for checking the rendered result.
- Before introducing a CSS selector, check all existing usages. After editing, inspect the cascade and verify that the selector is scoped to the intended page/component.
- Keep a stable baseline for the home page. When editing an internal page or admin view, compare the home page and verify its markup, hero, navigation and shared components remain unchanged.
- For Filament forms, count visible card/panel levels in the rendered DOM, not only PHP `Section` calls. A passing form test is insufficient if it does not check the visible hierarchy, width and actions.
- For multilingual content, verify RU, RO and EN with the same viewport. Check line wrapping, active state, fallback text and button/action alignment; never assume equal string lengths imply equal layout.
- For uploads and deployment-sensitive behavior, distinguish source code from runtime state. If the code and tests disagree with the server message, check deployed files, route/view caches, OPcache and storage links before changing validation logic.

### Before reporting completion

- Re-read the user request and verify every explicit requirement and every non-goal. Report any unverified item instead of calling the work complete.
- Verify the changed state, not only the default state: closed/open mobile menu, selected/unselected tabs, empty/populated content, draft/published content, image present/fallback image and all supported languages as applicable.
- Use evidence at the same layer as the change: authenticated admin screenshots for Filament work, public screenshots for public pages, and a real storage URL for uploaded images. HTTP 200, PHP tests or a compiled Blade view alone do not prove visual correctness.
- For a visual change, compare the first, middle and last repeated items and check: equal edges, widths, image ratios, bottom actions, no horizontal overflow and readable contrast.
- Run `composer test`, `git diff --check` and the full visual audit. If a required screen is outside the audit, add a focused regression test or inspect it manually before handoff.
- In the final report, state changed files, checks actually run, and any limitation. Never claim that a screenshot or server deployment was verified if it was not.

### After user rejects a change

- Stop extending the rejected variant. Identify the exact current state with `git diff`, the rendered page and the user's last accepted reference, then make a narrow corrective change.
- Record the rejected pattern as a project rule only when it is generalizable (for example: no green section fills, no nested Filament cards, no home-page changes during internal-page work). Do not preserve one-off screenshot coordinates as universal CSS.
