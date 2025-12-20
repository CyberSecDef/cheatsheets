<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cheatsheets</title>

    <!-- Frameworks and Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" id="prism-theme-dark" disabled>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" id="prism-theme-light">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: var(--bs-body-bg);
            transition: background-color 0.3s ease;
        }

        .cheatsheet-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            backdrop-filter: blur(10px);
            background-color: rgba(var(--bs-tertiary-bg-rgb), 0.8) !important;
        }

        .card-body+.card-body {
            border-top: 1px solid var(--bs-border-color);
        }

        pre[class*="language-"] {
            border: 1px solid var(--bs-border-color);
        }

        .category-toggle {
            cursor: pointer;
            user-select: none;
        }

        .category-toggle:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        .category-body {
            overflow: hidden;
            transition: max-height 0.25s ease, opacity 0.25s ease;
            opacity: 1;
        }

        .category-body.is-collapsed {
            opacity: 0;
        }

        .toc-target {
            scroll-margin-top: var(--cheatsheet-navbar-height, 80px);
        }
    </style>
</head>

<body>

    <!-- Sticky Header -->
    <nav class="navbar navbar-expand-lg border-bottom cheatsheet-navbar">
        <div class="container-fluid d-flex align-items-center justify-content-between">
			<div class="d-flex align-items-center">
				<div class="dropdown me-2">
                    <button
                        id="toc-toggle"
                        class="btn btn-outline-secondary"
                        type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="true"
                        aria-expanded="false"
                        aria-label="Table of contents"
                        title="Table of contents"
                    >
                        ☰
                    </button>
                    <ul class="dropdown-menu dropdown-menu-start" id="toc-menu" aria-labelledby="toc-toggle">
                        <li><h6 class="dropdown-header">Contents</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text text-muted">Loading...</span></li>
                    </ul>
                </div>

				<a class="navbar-brand" id="sticky-title" href="#">Cheat Sheets</a>
			</div>

			<div class="d-flex align-items-center">
				<div class="me-3">
                    <select
                        id="sheet-select"
                        class="form-select"
                        aria-label="Select Reference Sheet"
                        >
                        <?php

                        $dataDir = __DIR__ . '/data';
                        $jsonFiles = glob($dataDir . '/*.json') ?: [];
                        sort($jsonFiles, SORT_NATURAL | SORT_FLAG_CASE);

                        foreach ($jsonFiles as $jsonFile) {
                            $fileName = basename($jsonFile);
                            $displayName = pathinfo($fileName, PATHINFO_FILENAME);
                            $value = $fileName;

                            $fileContents = @file_get_contents($jsonFile);
                            if ($fileContents !== false) {
                                $decoded = json_decode($fileContents, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $title = $decoded['title'] ?? null;
                                    if (is_string($title) && $title !== '') {
                                        $displayName = $title;
                                    }
                                }
                            }

                            echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">' .
                                htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') .
                                '</option>';
                        }
                        ?>
                    </select>
                </div>
				<div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="theme-toggle">
                    <label class="form-check-label" for="theme-toggle"><i class="bi bi-moon"></i></label>
                </div>
			</div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">

        <div class="container py-0">
            <div class="row">
                <div class="p-4 p-md-5 mb-4 rounded-5 text-body-emphasis bg-body-secondary">
                    <div class="col px-0">
                        <h1 class="display-4 fst-italic" id="page-title"></h1>
                        <p class="lead my-3" id="page-description"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Masonry Grid -->
        <div class="row g-2" data-masonry='{"percentPosition": true}' id="masonry-grid">
            <!-- Cards will be dynamically inserted here -->
        </div>
    </main>

    <!-- Frameworks and Libraries JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
        const grid = document.getElementById('masonry-grid');

        const updateNavbarOffset = () => {
            const nav = document.querySelector('.cheatsheet-navbar');
            if (!nav) return;
            document.body.style.paddingTop = nav.offsetHeight + 'px';
            document.documentElement.style.setProperty('--cheatsheet-navbar-height', nav.offsetHeight + 'px');
        };

        window.addEventListener('load', updateNavbarOffset);
        window.addEventListener('resize', updateNavbarOffset);

        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const sheet = urlParams.get('sheet');
        
        const selectElement = document.getElementById('sheet-select');
        const optionExists = (select, value) => {
            if (!select || !value) return false;
            return [...select.options].some(option => option.value === value);
        };

        if (selectElement) {
            selectElement.addEventListener('change', (event) => {
                const selectedValue = event.target.value;
                const nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('sheet', selectedValue);
                window.location.href = nextUrl.toString();
            });
        }

        const getFirstOptionValue = (select) => {
            if (!select || !select.options || select.options.length === 0) return null;
            return select.options[0].value;
        };

        const defaultSheet = getFirstOptionValue(selectElement);
        const activeSheet = optionExists(selectElement, sheet)
            ? sheet
            : (optionExists(selectElement, defaultSheet) ? defaultSheet : getFirstOptionValue(selectElement));

        if (selectElement && activeSheet) {
            selectElement.value = activeSheet;
        }else{
            selectElement.options[0].selected = true;
        }

        if (!activeSheet) {
            console.error('No sheet options available; cannot load data.');
        } else {
        fetch('./data/' + activeSheet)
            .then(response => response.json()) // parses the JSON response into a JS object
            .then(d => {
                const data = d;
                document.getElementById('page-title').textContent = data.title;
                document.getElementById('page-description').textContent = data.description;

                const tocMenu = document.getElementById('toc-menu');
                const tocToggle = document.getElementById('toc-toggle');

                if (tocMenu) {
                    tocMenu.innerHTML = '';

                    const headerLi = document.createElement('li');
                    const headerEl = document.createElement('h6');
                    headerEl.className = 'dropdown-header';
                    headerEl.textContent = 'Contents';
                    headerLi.appendChild(headerEl);
                    tocMenu.appendChild(headerLi);

                    const dividerLi = document.createElement('li');
                    dividerLi.innerHTML = '<hr class="dropdown-divider">';
                    tocMenu.appendChild(dividerLi);
                }

                const layoutMasonry = () => {
                    try {
                        if (window.Masonry && typeof Masonry.data === 'function') {
                            const msnry = Masonry.data(grid);
                            if (msnry) msnry.layout();
                        }
                    } catch {
                        // no-op
                    }
                };

                data.categories.forEach((category, categoryIndex) => {
                    const col = document.createElement('div');
                    col.className = 'col-sm-12 col-md-6 col-lg-4 mb-4';

                    let itemsHtml = '';
                    category.items.forEach(item => {
                        let tableHtml = '';
                        if (item.table && item.table.headers && item.table.rows) {
                            tableHtml = `
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>${item.table.headers.map(h => `<th>${h}</th>`).join('')}</tr>
                                    </thead>
                                    <tbody>
                                        ${item.table.rows.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}
                                    </tbody>
                                </table>
                            </div>`;
                        }

                        let exampleHtml = '';
                        if (item.example) {
                            exampleHtml = `<pre class="my-2 py-2"><code style="overflow:scroll;" class="language-${data.language}">${item.example.replace(/</g, "&lt;").replace(/>/g, "&gt;")}<br /><br /></code></pre>`;
                        }

                        const itemDescription = item.description ? `<p class="card-text">${item.description}</p>` : '';

                        itemsHtml += `
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            ${itemDescription}
                            ${tableHtml}
                            ${exampleHtml}
                        </div>`;
                    });

                    const categoryDescription = category.description ? `<div class="card-body"><p class="card-text">${category.description}</p></div>` : '';

                    const headerId = `category-title-${categoryIndex}`;
                    const bodyId = `category-body-${categoryIndex}`;

                    col.innerHTML = `
                    <div class="card">
                        <h5
                            id="${headerId}"
                            class="card-header text-bg-secondary category-toggle toc-target"
                            role="button"
                            tabindex="0"
                            aria-expanded="true"
                            aria-controls="${bodyId}"
                        >${category.title}</h5>
                        <div class="category-body my-4 py-2" id="${bodyId}">
                            ${categoryDescription}
                            ${itemsHtml}
                        </div>
                    </div>`;

                    grid.appendChild(col);

                    const headerEl = col.querySelector('.category-toggle');
                    const bodyEl = col.querySelector('.category-body');

                    if (tocMenu) {
                        const li = document.createElement('li');
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'dropdown-item';
                        btn.textContent = category.title || `Category ${categoryIndex + 1}`;
                        btn.addEventListener('click', () => {
                            const target = document.getElementById(headerId);
                            if (target) {
                                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            if (window.bootstrap && tocToggle) {
                                try {
                                    window.bootstrap.Dropdown.getOrCreateInstance(tocToggle).hide();
                                } catch {
                                    // no-op
                                }
                            }
                        });
                        li.appendChild(btn);
                        tocMenu.appendChild(li);
                    }

                    if (headerEl && bodyEl) {
                        // Initialize to expanded with a concrete max-height so collapse animates.
                        bodyEl.classList.remove('is-collapsed');
                        bodyEl.style.maxHeight = bodyEl.scrollHeight + 'px';

                        const setExpanded = (expanded) => {
                            headerEl.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                            if (!expanded) {
                                bodyEl.classList.add('is-collapsed');
                                bodyEl.style.maxHeight = '0px';
                                return;
                            }

                            bodyEl.classList.remove('is-collapsed');
                            // Ensure we recalc in case content height changed.
                            bodyEl.style.maxHeight = bodyEl.scrollHeight + 'px';
                        };

                        const toggleExpanded = () => {
                            const isExpanded = headerEl.getAttribute('aria-expanded') === 'true';
                            setExpanded(!isExpanded);
                        };

                        headerEl.addEventListener('click', toggleExpanded);
                        headerEl.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                toggleExpanded();
                            }
                        });

                        bodyEl.addEventListener('transitionend', (e) => {
                            if (e.propertyName === 'max-height') {
                                layoutMasonry();
                            }
                        });
                    }
                });

                if (tocMenu && (!data.categories || data.categories.length === 0)) {
                    const li = document.createElement('li');
                    const span = document.createElement('span');
                    span.className = 'dropdown-item-text text-muted';
                    span.textContent = 'No categories available';
                    li.appendChild(span);
                    tocMenu.appendChild(li);
                }

                Prism.highlightAll();
                
            })
            .catch(error => console.error('Error fetching data:', error)); // Handle any errors
        }

        window.onload = () => {
            if (!selectElement) return;

            // Preserve the currently selected value
            const originalSelection = selectElement.value;

            // Convert the HTMLOptionsCollection to an array using the spread operator
            const options = [...selectElement.options];

            // Sort the array of options by their textContent
            options.sort((a, b) => {
                const textA = a.textContent.toLowerCase();
                const textB = b.textContent.toLowerCase();

                // Use localeCompare for robust alphabetical sorting
                return textA.localeCompare(textB);
            });

            // Empty the original select element
            selectElement.innerHTML = '';

            // Re-append the sorted options to the select element
            options.forEach(option => {
                selectElement.appendChild(option);
            });

            // Restore the original selection
            selectElement.value = originalSelection
        };

        const themeToggle = document.getElementById('theme-toggle');
        const prismLight = document.getElementById('prism-theme-light');
        const prismDark = document.getElementById('prism-theme-dark');

        const getPreferredTheme = () => {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme) return storedTheme;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };

        const setTheme = (theme) => {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                prismLight.disabled = true;
                prismDark.disabled = false;
                themeToggle.checked = true;
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                prismLight.disabled = false;
                prismDark.disabled = true;
                themeToggle.checked = false;
            }
        };

        setTheme(getPreferredTheme());

        themeToggle.addEventListener('change', () => {
            const theme = themeToggle.checked ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            setTheme(theme);
        });
    </script>
</body>

</html>