# IONOS Theming — How it works

A technical guide to understanding how Nextcloud applies visual styles and where IONOS customizations live.

---

## How NC theming works end-to-end

### 1. Theme registration

`apps-custom/nc_theming` registers `OverrideDefaultTheme` as the active theme via the NC theming app's ITheme interface. NC discovers all registered themes and applies the one configured in settings.

### 2. PHP generates a CSS variables block

When a page loads, NC's `ThemingController::getThemeStylesheet()` calls `$theme->getCSSVariables()` on the active theme. It iterates the returned array and builds a `:root { --var: value; ... }` string, serving it as a dynamic CSS file at:

```
/apps/theming/css/default.css?themeId=...
```

This file is injected into the page `<head>` early, before app CSS files.

### 3. App CSS files load after

Static CSS files from the nc_theming app (`css/variables.css`, `css/buttons.css`, etc.) are registered as app assets and loaded as `<link>` tags **after** the PHP-generated stylesheet.

### 4. CSS always wins

Both the PHP-generated stylesheet and `variables.css` target `:root`. CSS specificity is equal. **The last one loaded wins.** Since app CSS files always load after the PHP-generated file, any variable defined in CSS overrides the PHP value silently.

**Consequence:** `getCSSVariables()` in PHP was being overridden by `variables.css` for every variable both files defined. This is why we deleted it.

---

## Current architecture (after refactor)

```
variables.css  ← single source of truth for ALL CSS variables
    │
    ├── IONOS color palette   (--ion-color-blue-b4: #1474c4; ...)
    │       raw hex values, no references
    │
    ├── IONOS semantic tokens  (--ion-button-primary-background-default: ...)
    │       reference the palette via var()
    │
    └── NC semantic overrides  (--color-primary-element: ...; ...)
            map IONOS tokens onto NC's own variable names
            NC components (NcButton, NcAppNavigation, etc.) read these
```

`OverrideDefaultTheme.php` now contains only:
- `getTitle()` / `getDescription()` / `getMeta()` — theme metadata
- `getCustomCss()` — returns the Open Sans `@font-face` CSS rules

`getCSSVariables()` was deleted entirely.

---

## Server-side SVG icon recoloring (PHP)

NC has a mechanism where icons served via the theming endpoint are **recolored in PHP** before being sent to the browser. `IconBuilder::colorizeSvg()` replaces the hardcoded `#0082c9` fill in SVG source with the theme's `$primaryColor`:

```php
// apps/theming/lib/Util.php
public function colorizeSvg($svg, $color) {
    $svg = preg_replace('/#0082c9/i', $color, $svg);
    return $svg;
}
```

`$primaryColor` comes from `DefaultTheme::$primaryColor`, which is set in the constructor from `ThemingDefaults::getColorPrimary()`. In a vanilla NC install this defaults to `#745bca` (purple).

**In the IONOS theme this mechanism is not used.** Icons are instead colored via CSS `filter` properties defined in `variables.css`:

```css
--ion-icon-filter-blue-b4: brightness(0) saturate(100%) invert(37%) sepia(26%) ...;
```

These filters are applied in component CSS (`buttons.css`, `navigation.css`, etc.) to recolor white/black SVG icons to the correct IONOS blue. This approach is pure CSS — no PHP, no server round-trip per icon.

**Proof:** Removing `$this->primaryColor = '#003d8f'` from PHP produced no visible change (no purple icons), confirming PHP's recoloring system is bypassed entirely by the CSS filter approach.

---

## How NC components use the CSS variables

NC's Vue components read the semantic `--color-*` variables directly. Examples:

| Component | Variable used | What it controls |
|-----------|--------------|-----------------|
| `NcButton` (primary) | `--color-primary-element` | background color |
| `NcButton` (primary) | `--color-primary-element-hover` | hover background |
| `NcAppNavigation` | `--color-background-plain` | sidebar background |
| `NcAppNavigation` | `--color-primary-element` | active item highlight |
| All text | `--color-main-text` | default text color |

This is why the IONOS `variables.css` maps IONOS tokens onto NC's semantic variables — so NC components automatically get the right IONOS colors without any component-level CSS overrides.

---

## Where to make changes

| Change | File |
|--------|------|
| Adjust an IONOS color | `apps-custom/nc_theming/css/variables.css` — edit the hex in the palette section |
| Change which NC variable a token maps to | `variables.css` — bottom section (NC semantic overrides) |
| Add a new IONOS-specific component style | The relevant `css/*.css` file, referencing `--ion-*` tokens |
| Add Open Sans font variants | `OverrideDefaultTheme.php` → `getCustomCss()` |

---

## NC theme cache

NC caches the CSS output from `getCSSVariables()`. After PHP changes you need to bust it:

```bash
php occ maintenance:repair
# or
php occ theming:update-stylesheet
```

CSS file changes (`variables.css` etc.) do **not** require a cache bust — they are served as static assets with normal browser cache headers.
