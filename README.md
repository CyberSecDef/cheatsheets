# Cheatsheets (PHP + JSON)

A tiny PHP “applet” that renders cheat sheets from JSON files in `data/`.

- The dropdown is populated server-side by scanning `data/*.json`.
- The page loads the selected sheet client-side via `fetch()` and renders cards.

## Requirements

- PHP 8.x (should work on most PHP 7.4+ installs as well)
- A browser

## Run locally

From the project root:

```bash
php -S localhost:8000
```

Then open:

- http://localhost:8000/

## Usage

- Use the dropdown to switch sheets.
- You can also link directly to a sheet via the query param:

```text
/?sheet=sql.json
```

The app enforces that `sheet` must match one of the dropdown option values.

## Project structure

```text
index.php
data/
  git.json
  go_file.json
  python_file.json
  sql.json
```

## Adding a new cheatsheet

1. Create a new JSON file in `data/` ending with `.json`.
2. Add a top-level `title` field (used as the dropdown label).
3. Refresh the page.

Minimal example:

```json
{
  "title": "My Sheet",
  "description": "Short description shown under the title",
  "language": "bash",
  "categories": [
    {
      "title": "Basics",
      "description": "Optional category description",
      "items": [
        {
          "title": "Example",
          "description": "Optional item description",
          "example": "echo hello"
        }
      ]
    }
  ]
}
```

### Supported fields (current behavior)

- `title` (string): required; page title + dropdown label
- `description` (string): optional; shown under the title
- `language` (string): used for Prism code highlighting (`language-<language>`)
- `categories` (array): each category renders as a card
  - `category.title` (string)
  - `category.description` (string, optional)
  - `category.items` (array)
    - `item.title` (string)
    - `item.description` (string, optional)
    - `item.example` (string, optional): rendered as a code block
    - `item.table` (object, optional):
      - `headers` (array of strings)
      - `rows` (array of string arrays)

## Notes / caveats

- The UI is rendered client-side with `innerHTML` from JSON content. Keep JSON files trusted (not user-submitted) unless you harden rendering/escaping.
- The dropdown options are built by reading each JSON file’s top-level `title`. If a file is invalid JSON or has no `title`, the filename (without extension) is used.
