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

    <style>
        body {
            background-color: var(--bs-body-bg);
            transition: background-color 0.3s ease;
        }

        .navbar {
            position: sticky;
            top: 0;
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
    </style>
</head>

<body>

    <!-- Sticky Header -->
    <nav class="navbar navbar-expand-lg border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" id="sticky-title" href="#"></a>
            <div class="ms-auto d-flex align-items-center">
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
                    <label class="form-check-label" for="theme-toggle">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">

        <div class="container py-0">
            <div class="row">
                <div class="p-4 p-md-5 mb-4 rounded-5 text-body-emphasis bg-body-secondary">
                    <div class="col-8 px-0">
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

        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const sheet = urlParams.get('sheet');
        const defaultSheet = 'go_file.json';

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

        const activeSheet = optionExists(selectElement, sheet)
            ? sheet
            : (optionExists(selectElement, defaultSheet) ? defaultSheet : getFirstOptionValue(selectElement));

        if (selectElement && activeSheet) {
            selectElement.value = activeSheet;
        }

        if (!activeSheet) {
            console.error('No sheet options available; cannot load data.');
        } else {
        fetch('./data/' + activeSheet)
            .then(response => response.json()) // parses the JSON response into a JS object
            .then(d => {
                const data = d;
                document.getElementById('page-title').textContent = data.title;
                document.getElementById('sticky-title').textContent = data.title;
                document.getElementById('page-description').textContent = data.description;

                data.categories.forEach(category => {
                    const col = document.createElement('div');
                    col.className = 'col-4 mb-4';

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
                            exampleHtml = `
                            <pre class="mt-2"><code class="language-${data.language}">${item.example.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</code></pre>
                        `;
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

                    col.innerHTML = `
                    <div class="card ">
                        <h5 class="card-header text-bg-secondary">${category.title}</h5>
                        <p class="bg-transparent">
                        ${categoryDescription}
                        ${itemsHtml}
                        </p>
                    </div>`;

                    grid.appendChild(col);
                });

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