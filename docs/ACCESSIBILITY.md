# Accessibility verification

The interface uses native landmarks, labels, tables, headings, status messages, skip links, visible focus indicators, reduced-motion support, and descriptive button/image labels.

For each release, verify these checks in Chrome or Edge with only the keyboard and with NVDA:

- Tab from the address bar and confirm “Skip to main content” appears and moves focus into the page.
- Reach every navigation item, form control, status action, dialog-like editor, and logout control with Tab and Shift+Tab.
- Activate links and buttons with Enter or Space; confirm focus does not become trapped.
- Confirm every input announces its label, validation errors are announced, and status changes use understandable text.
- Navigate each page by landmarks and headings in NVDA; check lists, timeline steps, and data-table headers.
- Zoom to 200% at 1280px width and confirm content remains usable without two-dimensional scrolling, except wide data tables.
- Test Windows High Contrast mode and reduced-motion preference.
- Run an automated axe or Lighthouse accessibility scan against the public home, job search/detail, login/register, and each role dashboard; treat automated results as supplements to keyboard and screen-reader checks.
