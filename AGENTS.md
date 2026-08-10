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
