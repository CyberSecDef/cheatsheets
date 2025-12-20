#!/usr/bin/env python3
import json
import sys
from pathlib import Path


def fail(message: str) -> None:
    print(f"ERROR: {message}", file=sys.stderr)
    sys.exit(1)


def is_string_or_none(value):
    return value is None or isinstance(value, str)


def validate_table(table, context: str):
    if not isinstance(table, dict):
        fail(f"{context}: table must be an object")

    headers = table.get("headers")
    rows = table.get("rows")

    if not isinstance(headers, list) or not all(isinstance(h, str) for h in headers):
        fail(f"{context}: table.headers must be an array of strings")
    if not isinstance(rows, list):
        fail(f"{context}: table.rows must be an array")

    width = len(headers)
    for i, row in enumerate(rows):
        if not isinstance(row, list) or not all(isinstance(c, str) for c in row):
            fail(f"{context}: table.rows[{i}] must be an array of strings")
        if len(row) != width:
            fail(
                f"{context}: table.rows[{i}] has {len(row)} cols, expected {width} (headers={headers})"
            )


def validate_item(item, context: str):
    if not isinstance(item, dict):
        fail(f"{context}: item must be an object")

    title = item.get("title")
    if not isinstance(title, str) or not title:
        fail(f"{context}: item.title must be a non-empty string")

    if "description" not in item or not is_string_or_none(item.get("description")):
        fail(f"{context}: item.description must be a string or null")

    if "example" in item and not is_string_or_none(item.get("example")):
        fail(f"{context}: item.example must be a string or null")

    if "table" in item and item["table"] is not None:
        validate_table(item["table"], f"{context}.{title}.table")


def validate_category(cat, context: str):
    if not isinstance(cat, dict):
        fail(f"{context}: category must be an object")

    title = cat.get("title")
    if not isinstance(title, str) or not title:
        fail(f"{context}: category.title must be a non-empty string")

    if "description" not in cat or not is_string_or_none(cat.get("description")):
        fail(f"{context}.{title}: category.description must be a string or null")

    items = cat.get("items")
    if not isinstance(items, list):
        fail(f"{context}.{title}: category.items must be an array")

    for idx, item in enumerate(items):
        validate_item(item, f"{context}.{title}.items[{idx}]")


def validate_root(obj, context: str):
    if not isinstance(obj, dict):
        fail(f"{context}: root must be an object")

    for key in ["title", "description", "language", "categories"]:
        if key not in obj:
            fail(f"{context}: missing required key '{key}'")

    if not isinstance(obj.get("title"), str) or not obj["title"]:
        fail(f"{context}: title must be a non-empty string")
    if not isinstance(obj.get("description"), str):
        fail(f"{context}: description must be a string")
    if not isinstance(obj.get("language"), str):
        fail(f"{context}: language must be a string")

    categories = obj.get("categories")
    if not isinstance(categories, list):
        fail(f"{context}: categories must be an array")

    for idx, cat in enumerate(categories):
        validate_category(cat, f"{context}.categories[{idx}]")


def main():
    if len(sys.argv) != 2:
        print(f"Usage: {Path(sys.argv[0]).name} path/to/file.json", file=sys.stderr)
        return 2

    path = Path(sys.argv[1])
    if not path.exists():
        fail(f"File not found: {path}")

    try:
        text = path.read_text(encoding="utf-8")
        obj = json.loads(text)
    except json.JSONDecodeError as e:
        fail(f"Invalid JSON: {path}: {e}")
    except Exception as e:
        fail(f"Failed to read/parse: {path}: {e}")

    validate_root(obj, str(path))
    print(f"OK: {path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
