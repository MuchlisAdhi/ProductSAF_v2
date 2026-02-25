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

<div class="modal fade" id="lucideIconModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Pilih Ikon Kategori</h5>
                    <small class="text-muted">Pilih nama ikon Lucide.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <div id="lucide-icon-nav" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-3">
                    <input
                        type="text"
                        id="lucide-icon-search"
                        placeholder="Cari nama ikon..."
                        class="form-control"
                    >
                </div>

                <div id="lucide-icon-grid" class="row g-2"
                    data-icon-list='@json($iconOptions->values())'
                    data-icon-src-template="{{ $iconSvgTemplate }}"
                ></div>

                <div id="lucide-icon-empty-state" class="alert alert-secondary mt-3 d-none mb-0">
                    Tidak ada ikon yang sesuai dengan filter Anda.
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <p id="lucide-icon-pagination-info" class="mb-0 small text-muted">Page 1 of 1</p>
                <div class="d-flex gap-2">
                    <button type="button" id="lucide-icon-prev-page" class="btn btn-sm btn-outline-secondary">
                        Sebelumnya
                    </button>
                    <button type="button" id="lucide-icon-next-page" class="btn btn-sm btn-outline-secondary">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const bootstrapInstance = window.bootstrap;
        if (!bootstrapInstance || !bootstrapInstance.Modal) {
            return;
        }

        const modalElement = document.getElementById('lucideIconModal');
        const searchInput = document.getElementById('lucide-icon-search');
        const nav = document.getElementById('lucide-icon-nav');
        const grid = document.getElementById('lucide-icon-grid');
        const emptyState = document.getElementById('lucide-icon-empty-state');
        const paginationInfo = document.getElementById('lucide-icon-pagination-info');
        const prevPageButton = document.getElementById('lucide-icon-prev-page');
        const nextPageButton = document.getElementById('lucide-icon-next-page');

        if (!modalElement || !searchInput || !nav || !grid || !emptyState || !paginationInfo || !prevPageButton || !nextPageButton) {
            return;
        }

        const modal = new bootstrapInstance.Modal(modalElement);
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
            button.className = 'btn btn-sm';
            button.dataset.category = categoryId;
            button.textContent = `${label} (${count})`;

            const isActive = activeCategory === categoryId;
            if (isActive) {
                button.classList.add('btn-primary');
            } else {
                button.classList.add('btn-outline-secondary');
            }

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
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';

            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('data-icon-option', '');
            button.setAttribute('data-icon-value', iconName);
            button.className = 'btn btn-outline-secondary w-100 d-flex align-items-center text-start gap-2';

            const iconBox = document.createElement('span');
            iconBox.className = 'd-inline-flex align-items-center justify-content-center border rounded bg-light';
            iconBox.style.width = '28px';
            iconBox.style.height = '28px';

            const iconImage = document.createElement('img');
            iconImage.src = iconUrl(iconName);
            iconImage.alt = iconName;
            iconImage.loading = 'lazy';
            iconImage.className = 'img-fluid';
            iconImage.style.maxWidth = '16px';
            iconImage.style.maxHeight = '16px';

            const fallback = document.createElement('span');
            fallback.className = 'd-none small fw-bold';
            fallback.textContent = initials(iconName);

            iconImage.addEventListener('error', () => {
                iconImage.classList.add('d-none');
                fallback.classList.remove('d-none');
            });

            iconBox.appendChild(iconImage);
            iconBox.appendChild(fallback);

            const label = document.createElement('span');
            label.className = 'text-truncate';
            label.textContent = iconName;

            button.appendChild(iconBox);
            button.appendChild(label);

            button.addEventListener('click', () => pickIcon(iconName));

            col.appendChild(button);

            return col;
        };

        const renderPaginatedIcons = () => {
            const totalMatches = filteredIcons.length;
            const totalPages = Math.max(1, Math.ceil(totalMatches / ICONS_PER_PAGE));
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);

            const startIndex = (currentPage - 1) * ICONS_PER_PAGE;
            const visibleIcons = filteredIcons.slice(startIndex, startIndex + ICONS_PER_PAGE);

            grid.innerHTML = '';
            visibleIcons.forEach((iconName) => grid.appendChild(createIconButton(iconName)));

            emptyState.classList.toggle('d-none', totalMatches > 0);

            if (totalMatches === 0) {
                paginationInfo.textContent = 'Tidak ada ikon yang ditemukan.';
            } else {
                paginationInfo.textContent = `Halaman ${currentPage} dari ${totalPages} (${totalMatches} ikon)`;
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

                button.classList.toggle('btn-primary', isSelected);
                button.classList.toggle('btn-outline-secondary', !isSelected);
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
            iconImage.className = 'img-fluid';
            iconImage.style.maxWidth = '18px';
            iconImage.style.maxHeight = '18px';
            iconImage.addEventListener('error', () => {
                previewNode.innerHTML = `<span class="small fw-semibold">${initials(iconLabel)}</span>`;
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
            modal.hide();
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

        const openModal = (targetInputId) => {
            currentInputId = targetInputId;
            searchInput.value = '';
            activeCategory = 'all';
            renderCategories();
            applyFilters('');
            modal.show();
            setTimeout(() => searchInput.focus(), 150);
        };

        document.querySelectorAll('[data-open-icon-picker]').forEach((button) => {
            button.addEventListener('click', () => {
                const targetInput = button.getAttribute('data-target-input');
                if (!targetInput) return;
                openModal(targetInput);
            });
        });

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

        searchInput.addEventListener('input', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;
            applyFilters(target.value);
        });

        buildCategoryMap();
        renderCategories();
        applyFilters('');
    })();
</script>
@endpush
