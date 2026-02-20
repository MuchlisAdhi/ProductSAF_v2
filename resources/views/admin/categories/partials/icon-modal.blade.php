@php
    $normalizeIcon = static function (?string $value): string {
        return \Illuminate\Support\Str::of((string) $value)
            ->trim()
            ->replace(['_', '.'], '-')
            ->kebab()
            ->lower()
            ->toString();
    };

    $iconOptions = collect($lucideIcons ?? [])
        ->map(fn ($value) => $normalizeIcon((string) $value))
        ->filter()
        ->unique()
        ->values();

    $iconSvgTemplate = route('admin.lucide-icons.svg', ['name' => '__ICON__']);
@endphp

<div id="lucide-icon-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-xl">
        <header class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-6">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Select Category Icon</h3>
                <p class="text-xs text-slate-600">Choose a Lucide icon name.</p>
            </div>
            <button
                type="button"
                id="close-lucide-icon-modal"
                class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
            >
                Close
            </button>
        </header>

        <div class="space-y-3 p-4 sm:p-6">
            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-700">Categories</p>
                <div id="lucide-icon-nav" class="flex gap-2 overflow-x-auto pb-1"></div>
            </div>

            <input
                type="text"
                id="lucide-icon-search"
                placeholder="Search icon name..."
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            >

            <div
                id="lucide-icon-grid"
                data-icon-list='@json($iconOptions->values())'
                data-icon-src-template="{{ $iconSvgTemplate }}"
                class="grid max-h-[24rem] grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3 lg:grid-cols-4"
            ></div>

            <p id="lucide-icon-empty-state" class="hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                No icons match your filters.
            </p>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p id="lucide-icon-pagination-info" class="text-xs text-slate-600">Page 1 of 1</p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="lucide-icon-prev-page"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Prev
                    </button>
                    <button
                        type="button"
                        id="lucide-icon-next-page"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const modal = document.getElementById('lucide-icon-modal');
        const closeButton = document.getElementById('close-lucide-icon-modal');
        const searchInput = document.getElementById('lucide-icon-search');
        const nav = document.getElementById('lucide-icon-nav');
        const grid = document.getElementById('lucide-icon-grid');
        const emptyState = document.getElementById('lucide-icon-empty-state');
        const paginationInfo = document.getElementById('lucide-icon-pagination-info');
        const prevPageButton = document.getElementById('lucide-icon-prev-page');
        const nextPageButton = document.getElementById('lucide-icon-next-page');
        if (!modal || !closeButton || !searchInput || !nav || !grid || !emptyState || !paginationInfo || !prevPageButton || !nextPageButton) return;

        const normalize = (value) => String(value || '').trim().toLowerCase();
        const initials = (value) => {
            const normalized = normalize(value);
            if (!normalized) return 'N/A';
            return normalized
                .split('-')
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part.charAt(0).toUpperCase())
                .join('');
        };

        const ICONS_PER_PAGE = 40;
        const CATEGORY_RULES = [
            { id: 'all', label: 'All' },
            { id: 'accessibility', label: 'Accessibility', terms: ['accessibility', 'eye', 'ear', 'hearing', 'contrast', 'languages'] },
            { id: 'arrows', label: 'Arrows', terms: ['arrow', 'chevron', 'move', 'corner', 'route'] },
            { id: 'alerts', label: 'Alerts', terms: ['alert', 'alarm', 'bell', 'shield-alert', 'triangle-alert'] },
            { id: 'communication', label: 'Communication', terms: ['message', 'phone', 'mail', 'send', 'contact', 'inbox'] },
            { id: 'devices', label: 'Devices', terms: ['smartphone', 'tablet', 'monitor', 'laptop', 'watch', 'mouse', 'keyboard', 'tv'] },
            { id: 'files', label: 'Files', terms: ['file', 'folder', 'clipboard', 'book', 'archive', 'receipt'] },
            { id: 'charts', label: 'Charts', terms: ['chart', 'pie', 'bar-chart', 'line-chart', 'trending', 'activity'] },
            { id: 'weather', label: 'Weather', terms: ['sun', 'moon', 'cloud', 'wind', 'snow', 'umbrella', 'thermometer'] },
            { id: 'travel', label: 'Travel', terms: ['car', 'bus', 'truck', 'plane', 'train', 'ship', 'map', 'compass'] },
            { id: 'media', label: 'Media', terms: ['play', 'pause', 'stop', 'volume', 'music', 'mic', 'camera', 'video'] },
            { id: 'users', label: 'Users', terms: ['user', 'users', 'person', 'contact', 'badge'] },
            { id: 'commerce', label: 'Commerce', terms: ['shopping', 'cart', 'wallet', 'credit-card', 'banknote', 'coins', 'tag'] },
            { id: 'shapes', label: 'Shapes', terms: ['circle', 'square', 'triangle', 'hexagon', 'diamond', 'star'] },
            { id: 'other', label: 'Other' },
        ];

        let allIcons = [];
        try {
            const payload = String(grid.getAttribute('data-icon-list') || '[]');
            const parsed = JSON.parse(payload);
            allIcons = Array.isArray(parsed)
                ? parsed.map((item) => normalize(item)).filter((item) => item !== '')
                : [];
        } catch (error) {
            allIcons = [];
        }

        const srcTemplate = String(grid.getAttribute('data-icon-src-template') || '');
        const categoryMap = new Map();

        let filteredIcons = [...allIcons];
        let currentPage = 1;
        let currentInputId = null;
        let activeCategory = 'all';

        const buildCategoryMap = () => {
            CATEGORY_RULES.forEach((rule) => categoryMap.set(rule.id, []));

            allIcons.forEach((iconName) => {
                let resolvedCategory = 'other';

                for (const rule of CATEGORY_RULES) {
                    if (!Array.isArray(rule.terms) || rule.terms.length === 0) continue;
                    if (rule.terms.some((term) => iconName.includes(term))) {
                        resolvedCategory = rule.id;
                        break;
                    }
                }

                categoryMap.get(resolvedCategory).push(iconName);
            });

            categoryMap.set('all', [...allIcons]);
        };

        const iconUrl = (iconName) => srcTemplate.replace('__ICON__', encodeURIComponent(iconName));

        const getIconsByCategory = (categoryId) => {
            if (categoryId === 'all') return [...allIcons];
            return [...(categoryMap.get(categoryId) || [])];
        };

        const createNavButton = (categoryId, label, count) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'whitespace-nowrap rounded-full border px-3 py-1 text-xs font-semibold transition';
            button.dataset.category = categoryId;
            button.textContent = `${label} (${count})`;

            const isActive = activeCategory === categoryId;
            button.classList.toggle('border-emerald-500', isActive);
            button.classList.toggle('bg-emerald-50', isActive);
            button.classList.toggle('text-emerald-700', isActive);
            button.classList.toggle('border-slate-300', !isActive);
            button.classList.toggle('bg-white', !isActive);
            button.classList.toggle('text-slate-700', !isActive);

            button.addEventListener('click', () => {
                activeCategory = categoryId;
                currentPage = 1;
                renderCategories();
                applyFilters(searchInput.value);
            });

            return button;
        };

        const renderCategories = () => {
            nav.innerHTML = '';

            CATEGORY_RULES.forEach((rule) => {
                const count = getIconsByCategory(rule.id).length;
                if (rule.id !== 'all' && count === 0) return;
                nav.appendChild(createNavButton(rule.id, rule.label, count));
            });
        };

        const createIconButton = (iconName) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('data-icon-option', '');
            button.setAttribute('data-icon-value', iconName);
            button.className = 'flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm font-medium text-slate-700 hover:border-emerald-300 hover:bg-emerald-50';

            const iconBox = document.createElement('span');
            iconBox.className = 'grid h-5 w-5 shrink-0 place-items-center rounded-md bg-emerald-50';

            const iconImage = document.createElement('img');
            iconImage.src = iconUrl(iconName);
            iconImage.alt = iconName;
            iconImage.loading = 'lazy';
            iconImage.className = 'h-4 w-4';

            const fallback = document.createElement('span');
            fallback.className = 'hidden text-[10px] font-bold text-emerald-700';
            fallback.textContent = initials(iconName);

            iconImage.addEventListener('error', () => {
                iconImage.classList.add('hidden');
                fallback.classList.remove('hidden');
            });

            iconBox.appendChild(iconImage);
            iconBox.appendChild(fallback);

            const label = document.createElement('span');
            label.className = 'truncate';
            label.textContent = iconName;

            button.appendChild(iconBox);
            button.appendChild(label);

            button.addEventListener('click', () => pickIcon(iconName));

            return button;
        };

        const renderPaginatedIcons = () => {
            const totalMatches = filteredIcons.length;
            const totalPages = Math.max(1, Math.ceil(totalMatches / ICONS_PER_PAGE));
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);

            const startIndex = (currentPage - 1) * ICONS_PER_PAGE;
            const visibleIcons = filteredIcons.slice(startIndex, startIndex + ICONS_PER_PAGE);

            grid.innerHTML = '';
            visibleIcons.forEach((iconName) => grid.appendChild(createIconButton(iconName)));

            emptyState.classList.toggle('hidden', totalMatches > 0);

            if (totalMatches === 0) {
                paginationInfo.textContent = 'No icons found.';
            } else {
                paginationInfo.textContent = `Page ${currentPage} of ${totalPages} (${totalMatches} icons)`;
            }

            prevPageButton.disabled = totalMatches === 0 || currentPage <= 1;
            nextPageButton.disabled = totalMatches === 0 || currentPage >= totalPages;

            updateSelectionStyles();
        };

        const updateSelectionStyles = () => {
            if (!currentInputId) return;
            const input = document.getElementById(currentInputId);
            const currentValue = normalize(input ? input.value : '');

            Array.from(grid.querySelectorAll('[data-icon-option]')).forEach((button) => {
                const iconValue = normalize(button.getAttribute('data-icon-value'));
                const isSelected = currentValue !== '' && iconValue === currentValue;
                button.classList.toggle('border-emerald-500', isSelected);
                button.classList.toggle('bg-emerald-50', isSelected);
                button.classList.toggle('text-emerald-700', isSelected);
            });
        };

        const updatePreview = (inputId, iconLabel) => {
            const previewNode = document.querySelector(`[data-icon-preview][data-for-input="${inputId}"]`);
            const labelNode = document.querySelector(`[data-icon-label][data-for-input="${inputId}"]`);
            if (!previewNode || !labelNode) return;

            previewNode.innerHTML = '';
            const iconImage = document.createElement('img');
            iconImage.src = iconUrl(iconLabel);
            iconImage.alt = iconLabel;
            iconImage.className = 'h-5 w-5';
            iconImage.addEventListener('error', () => {
                previewNode.innerHTML = `<span class="text-[11px] font-semibold text-emerald-700">${initials(iconLabel)}</span>`;
            });
            previewNode.appendChild(iconImage);

            labelNode.textContent = iconLabel || 'No icon selected';
        };

        const pickIcon = (iconValue) => {
            if (!currentInputId) return;
            const input = document.getElementById(currentInputId);
            if (!input) return;

            const normalized = normalize(iconValue);
            if (normalized === '') return;

            input.value = normalized;
            updatePreview(currentInputId, normalized);
            closeModal();
        };

        const applyFilters = (query = '') => {
            const normalizedQuery = normalize(query);
            const sourceIcons = getIconsByCategory(activeCategory);

            filteredIcons = sourceIcons.filter((iconName) => {
                return normalizedQuery === '' || iconName.includes(normalizedQuery);
            });

            currentPage = 1;
            renderPaginatedIcons();
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentInputId = null;
        };

        const openModal = (targetInputId) => {
            currentInputId = targetInputId;
            searchInput.value = '';
            activeCategory = 'all';
            renderCategories();
            applyFilters('');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            searchInput.focus();
        };

        document.querySelectorAll('[data-open-icon-picker]').forEach((button) => {
            button.addEventListener('click', () => {
                const targetInput = button.getAttribute('data-target-input');
                if (!targetInput) return;
                openModal(targetInput);
            });
        });

        closeButton.addEventListener('click', closeModal);
        prevPageButton.addEventListener('click', () => {
            if (prevPageButton.disabled) return;
            currentPage -= 1;
            renderPaginatedIcons();
        });
        nextPageButton.addEventListener('click', () => {
            if (nextPageButton.disabled) return;
            currentPage += 1;
            renderPaginatedIcons();
        });
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        searchInput.addEventListener('input', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;
            applyFilters(target.value);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        buildCategoryMap();
        renderCategories();
        applyFilters('');
    })();
</script>
