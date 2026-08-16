(function () {
    const timers = new WeakMap();
    const builderStates = new Map();
    let suppressBuilderScrollUntil = 0;

    function getBuilderItems(builder) {
        return Array.from(builder.querySelectorAll(':scope > .fi-fo-builder-items > .fi-fo-builder-item'));
    }

    function getBuilderItemKey(item, index) {
        return item.getAttribute('wire:key')
            || item.getAttribute('x-sortable-item')
            || item.getAttribute('data-index')
            || `editorial-builder-item-${index}`;
    }

    function isVisible(element) {
        return element.offsetParent !== null || element.getClientRects().length > 0;
    }

    function scrollToNewBuilderItem(item) {
        window.requestAnimationFrame(() => {
            window.setTimeout(() => {
                if (Date.now() < suppressBuilderScrollUntil) {
                    return;
                }

                if (!item.isConnected || !isVisible(item)) {
                    return;
                }

                item.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    block: 'center',
                    inline: 'nearest',
                });
            }, 80);
        });
    }

    function getAddedBuilderItem(records) {
        for (const record of records) {
            for (const node of record.addedNodes) {
                if (!(node instanceof Element)) {
                    continue;
                }

                if (node.matches('.fi-fo-builder-item')) {
                    return node;
                }

                const item = node.querySelector('.fi-fo-builder-item');
                if (item) {
                    return item;
                }
            }
        }

        return null;
    }

    function syncBuilder(builder) {
        const builderId = builder.id || builder;
        const items = getBuilderItems(builder);
        const currentKeys = new Set(items.map(getBuilderItemKey).filter(Boolean));
        let state = builderStates.get(builderId);

        if (!state) {
            state = { element: builder, keys: currentKeys, itemCount: items.length, observer: null };
            builderStates.set(builderId, state);
        } else if (state.element !== builder) {
            state.observer?.disconnect();
            state.element = builder;
            const addedItem = items.find((item, index) => !state.keys.has(getBuilderItemKey(item, index)))
                || (items.length > state.itemCount ? items.at(-1) : null);
            state.keys = currentKeys;
            state.itemCount = items.length;

            if (addedItem) {
                scrollToNewBuilderItem(addedItem);
            }
        }

        if (state.observer) {
            return;
        }

        state.observer = new MutationObserver((records) => {
            const nextItems = getBuilderItems(builder);
            const nextKeys = new Set(nextItems.map(getBuilderItemKey).filter(Boolean));
            const addedItem = getAddedBuilderItem(records)
                || nextItems.find((item, index) => !state.keys.has(getBuilderItemKey(item, index)))
                || (nextItems.length > state.itemCount ? nextItems.at(-1) : null);

            state.keys = nextKeys;
            state.itemCount = nextItems.length;

            if (addedItem) {
                scrollToNewBuilderItem(addedItem);
            }
        });

        state.observer.observe(builder, { childList: true, subtree: true });
    }

    function syncBuilders(root = document) {
        root.querySelectorAll?.('.fi-page.fi-editorial-page .editorial-content-builder').forEach(syncBuilder);
    }

    function resetBuilderObservers() {
        builderStates.forEach((state) => state.observer?.disconnect());
        builderStates.clear();
    }

    function scheduleAutosave(event) {
        const editor = event.target.closest('.fi-editorial-page');
        if (!editor || !event.target.closest('input, textarea, select, [contenteditable="true"]')) {
            return;
        }

        const root = editor.closest('[wire\\:id]');
        if (!root) {
            return;
        }

        const component = window.Livewire?.find(root.getAttribute('wire:id'));
        if (!component) {
            return;
        }

        window.clearTimeout(timers.get(editor));
        timers.set(editor, window.setTimeout(() => component.call('autosaveDraft'), 2200));
    }

    document.addEventListener('input', scheduleAutosave, true);
    document.addEventListener('change', scheduleAutosave, true);

    function initializeBuilderObservers() {
        syncBuilders();

        const pageObserver = new MutationObserver(() => syncBuilders());
        pageObserver.observe(document.body, { childList: true, subtree: true });
    }

    function initializeEditorialStickyHeaders(root = document) {
        root.querySelectorAll?.('.fi-page.fi-editorial-page .fi-header').forEach((header) => {
            if (header.dataset.stickyHeaderReady === 'true') {
                return;
            }

            header.dataset.stickyHeaderReady = 'true';
            const sentinel = document.createElement('div');
            sentinel.className = 'editorial-header-sentinel';
            sentinel.setAttribute('aria-hidden', 'true');
            header.before(sentinel);

            new IntersectionObserver(([entry]) => {
                header.classList.toggle('is-stuck', !entry.isIntersecting);
            }, { threshold: 1 }).observe(sentinel);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeBuilderObservers();
            initializeEditorialStickyHeaders();
        }, { once: true });
    } else {
        initializeBuilderObservers();
        initializeEditorialStickyHeaders();
    }

    document.addEventListener('livewire:navigated', () => {
        resetBuilderObservers();
        syncBuilders();
        initializeEditorialStickyHeaders();
    });

    function refreshEditorialTranslationStatus() {
        const editor = document.querySelector('.fi-page.fi-editorial-page[wire\\:id]');
        const livewireId = editor?.getAttribute('wire:id');
        const component = livewireId ? window.Livewire?.find(livewireId) : null;

        if (component?.$refresh) {
            component.$refresh();
        }
    }

    window.addEventListener('editorial-translation-complete', () => {
        suppressBuilderScrollUntil = Date.now() + 1000;
        window.setTimeout(refreshEditorialTranslationStatus, 0);
    });
})();
