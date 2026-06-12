# IONOS Theming Refactor — Design Spec

**Date:** 2026-06-12  
**Branch:** ionos-theming-simplification  
**Goal:** Minimal, maintainable theming. NC semantic variables drive everything NC's component system understands. Custom CSS only for genuine IONOS deviations NC cannot express.

---

## Principle

> NC variables drive NC components. Custom CSS only for genuine deviations.

CSS always loads after NC's PHP-generated styles and wins on equal specificity. `variables.css` is the single source of truth. PHP `getCSSVariables()` is already deleted.

---

## 1. `variables.css`

Two layers only. No intermediate `--ion-button-*` tokens.

### Layer 1 — IONOS palette (raw hex, no `var()` references)

Keep all `--ion-color-*` values as-is. These are the single source of hex values for everything downstream.

### Layer 2 — NC semantic overrides

Map palette onto NC's own variable names. NC components read these natively — no CSS overrides needed for standard states.

| NC variable | Value | What it drives |
|---|---|---|
| `--color-primary-element` | `light-dark(blue-b7, blue-b4)` | NcButton primary bg, nav active item |
| `--color-primary-element-hover` | `light-dark(blue-b4, blue-b5)` | NcButton primary hover |
| `--color-primary-element-light` | `light-dark(blue-b1, blue-b7)` | NcButton tertiary bg |
| `--color-primary-element-light-text` | `light-dark(cool-grey-c8, cool-grey-c1)` | Text on tertiary buttons |
| `--color-primary-element-light-hover` | `light-dark(cool-grey-c2, cool-grey-c7)` | Tertiary hover bg |
| `--color-background-plain` | `light-dark(cool-grey-c1, cool-grey-c8)` | Sidebar background |
| `--color-background-hover` | `light-dark(blue-b1, cool-grey-c7)` | Nav hover, file list hover |
| `--color-main-background` | `light-dark(#fff, blue-b9)` | Page background |
| `--color-main-text` | `light-dark(cool-grey-c7, cool-grey-c1)` | Default text |
| `--color-text-maxcontrast` | same as main-text | High contrast text |
| `--color-primary` | `#003d8f` | NC internal primary reference |
| `--color-error/warning/success/info` | IONOS status colors | Feedback states |
| `--color-shadow-header` | IONOS shadow value | Header shadow |
| `--default-font-size` | `15px` | Base font size |
| `--font-face` | `"Open sans", Arial, ...` | Font stack |
| `--background-invert-if-bright` | `invert(100%)` | Icon display in light mode |

### Tokens to delete (NC semantics replace them)

- `--ion-button-primary-*` — covered by `--color-primary-element-*`
- `--ion-button-tertiary-*` — covered by `--color-primary-element-light-*`
- `--ion-button-sidebar-*` — covered by `--color-background-hover/plain` and `--color-main-text`
- `--ion-surface-primary` — covered by `--color-main-background`
- `--ion-surface-secondary` — covered by `--color-background-plain`
- `--ion-text` — covered by `--color-main-text`
- `--ion-color-typo-mild` — inline into `--color-main-text`

### Tokens to keep (no NC equivalent)

- `--ion-icon-filter-*` — CSS filter values for icon recoloring, no NC semantic exists
- `--ion-button-secondary-*` — outlined dark-navy style, NC has no outlined variant
- `--ion-files-list-*` — file list custom hover/active states
- `--ion-context-menu-*` — context menu custom styles
- `--ion-chip-*` — chip component
- `--ion-breadcrumb-*` — breadcrumb text states
- `--ion-surface-dialog` — dialog surface

---

## 2. `buttons.css`

### Delete

- All `.button-vue--vue-primary` color overrides — NC drives primary via `--color-primary-element`
- All `.button-vue--vue-tertiary` background overrides — NC drives tertiary via `--color-primary-element-light`
- All `--ion-button-sidebar-*` references — moved to navigation.css or handled by NC semantics
- All `--ion-button-tertiary-*` references

### Keep — structural rules (unchanged)

- `border-radius: 30px` on `.button-vue`
- `min-height`, `min-width`, `padding`, `gap`, `font-size`, `font-weight` on `.button-vue--icon-and-text`
- Icon sizing rules
- Mobile media queries

### Keep — secondary outlined (genuine IONOS deviation)

NC has no outlined button variant. Reference palette directly — no intermediate token:

```css
&.button-vue--vue-secondary {
  background-color: transparent;
  border: 2px solid light-dark(var(--ion-color-blue-b7), var(--ion-color-cool-grey-c1));
  color: light-dark(var(--ion-color-blue-b7), var(--ion-color-cool-grey-c1));

  &:hover { background-color: light-dark(var(--ion-color-blue-b7), ...); }
}
```

### Keep — icon-only hover/active (IONOS deviation from NC defaults)

Reference palette directly.

### Keep — modal, image editor, login overrides

Reference NC semantics (`--color-main-text`, `--color-background-hover`) or palette directly where NC has no equivalent.

---

## 3. `navigation.css`

### Delete / replace

All `--ion-button-sidebar-*` and `--ion-surface-*` token references — replace with NC semantics:

| Old token | Replace with |
|---|---|
| `--ion-surface-secondary` | `var(--color-background-plain)` |
| `--ion-surface-primary` | `var(--color-main-background)` |
| `--ion-button-sidebar-background-hover` | `var(--color-background-hover)` |
| `--ion-button-sidebar-background-active` | `var(--color-primary-element)` |
| `--ion-button-sidebar-text` | `var(--color-main-text)` |
| `--ion-button-sidebar--icon-only-background` | `var(--color-background-plain)` |
| `--ion-button-sidebar--icon-only-background-hover` | `var(--color-background-hover)` |
| `--ion-button-sidebar--icon-only-text` | `var(--color-main-text)` |

### Keep — structural rules (unchanged)

- `.app-navigation__search { display: none }`
- `.app-navigation-list { padding: 20px }`
- `border-radius: var(--border-radius-pill)` on nav entries
- Sub-navigation expanded section border-radius and padding
- Mobile media queries

---

## Files changed

| File | Change |
|---|---|
| `apps-custom/nc_theming/css/variables.css` | Delete 7 token groups, keep palette + NC overrides + remaining IONOS tokens |
| `apps-custom/nc_theming/css/buttons.css` | Delete primary/tertiary color overrides, rewrite secondary to reference palette directly |
| `apps-custom/nc_theming/css/navigation.css` | Replace all `--ion-*` color references with NC semantic variables |

No PHP changes. No other files touched.

---

## Success criteria

1. Primary buttons show IONOS dark-navy (`#0B2A63` light / `#1474c4` dark)
2. Secondary buttons show outlined dark-navy style
3. Tertiary buttons show light-blue (`#dbedf8`) background
4. Sidebar background is cool-grey-c1 (`#f4f7fa`) in light mode
5. Nav active item uses `--color-primary-element` (same dark-navy)
6. No references to deleted tokens remain in any CSS file
7. `variables.css` has no `--ion-button-primary-*`, `--ion-button-tertiary-*`, `--ion-button-sidebar-*`, `--ion-surface-primary/secondary` entries
