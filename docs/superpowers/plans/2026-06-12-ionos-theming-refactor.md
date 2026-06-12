# IONOS Theming Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor IONOS theming so NC semantic variables drive all standard components; custom CSS only for genuine IONOS deviations (secondary outlined button).

**Architecture:** Two-layer `variables.css` (palette → NC semantics). `buttons.css` drops primary/tertiary color rules — NC handles them via `--color-primary-element` and `--color-primary-element-light`. `navigation.css` replaces all `--ion-button-sidebar-*` / `--ion-surface-*` with NC semantics.

**Tech Stack:** CSS custom properties, Nextcloud theming app CSS cascade. All files in `apps-custom/nc_theming/css/`. No PHP changes.

---

## Files

- Modify: `apps-custom/nc_theming/css/variables.css`
- Modify: `apps-custom/nc_theming/css/buttons.css`
- Modify: `apps-custom/nc_theming/css/navigation.css`

---

## Task 0: Pre-check — scan all CSS files for tokens being deleted

Before changing anything, confirm which files reference the tokens we're removing.

- [ ] **Step 1: Scan all nc_theming CSS files**

Run:
```bash
grep -rn "ion-button-primary\|ion-button-tertiary\|ion-button-sidebar\|ion-surface-primary\b\|ion-surface-secondary\b\|--ion-text:\|ion-color-typo-mild\|ion-button--icon-only" apps-custom/nc_theming/css/
```

If output includes files **other than** `variables.css`, `buttons.css`, or `navigation.css` — those files must also be updated before the tokens can be deleted from `variables.css`. Fix them by replacing each token with the NC semantic equivalent from the table in the spec (`docs/superpowers/specs/2026-06-12-ionos-theming-refactor-design.md`), or with a palette variable directly.

If output only lists `variables.css`, `buttons.css`, `navigation.css` — proceed to Task 1.

---

## Task 1: Refactor `variables.css`

Remove 7 token groups replaced by NC semantics. Add 3 new NC semantic variables. Keep palette, secondary button tokens, and IONOS-specific tokens.

**Files:**
- Modify: `apps-custom/nc_theming/css/variables.css`

- [ ] **Step 1: Write the new `variables.css`**

Replace the entire file with:

```css
:root {
	/* ── IONOS color palette ──────────────────────────────────────────────── */
	--ion-color-blue-b1: #dbedf8;
	--ion-color-blue-b2: #95caeb;
	--ion-color-blue-b3: #3196D6;
	--ion-color-blue-b4: #1474c4;
	--ion-color-blue-b5: #095BB1;
	--ion-color-blue-b6: #003D8F;
	--ion-color-blue-b7: #0B2A63;
	--ion-color-blue-b8: #001B41;
	--ion-color-blue-b9: #02102B;
	--ion-color-cool-grey-c1: #f4f7fa;
	--ion-color-cool-grey-c2: #dbe2e8;
	--ion-color-cool-grey-c3: #bcc8d4;
	--ion-color-cool-grey-c4: #97A3B4;
	--ion-color-cool-grey-c5: #718095;
	--ion-color-cool-grey-c6: #465A75;
	--ion-color-cool-grey-c7: #2E4360;
	--ion-color-cool-grey-c8: #1D2D42;
	--ion-color-light-grey: #d7d7d7;
	--ion-color-green-g3: #12cf76;
	--ion-color-rose-r3: #ff6159;
	--ion-color-sky-s3: #11c7e6;
	--ion-color-amber-y3: #ffaa00;
	--ion-color-amber-y4: #EF8300;
	--ion-color-amber-y5: #c36b00;
	--ion-color-amber-y6: #8E4E00;
	--ion-color-main-background: light-dark(#fff, var(--ion-color-blue-b9));
	--ion-color-primary: #003d8f;
	--ion-color-secondary: #001B41;

	/* ── IONOS secondary button (outlined — no NC equivalent) ────────────── */
	--ion-button-secondary-background-default: transparent;
	--ion-button-secondary-background-hover: light-dark(var(--ion-color-blue-b7), var(--ion-color-cool-grey-c1));
	--ion-button-secondary-background-active: light-dark(var(--ion-color-secondary), var(--ion-color-cool-grey-c3));
	--ion-button-secondary-background-disabled: transparent;
	--ion-button-secondary-border-default: 2px solid light-dark(var(--ion-color-blue-b7), var(--ion-color-cool-grey-c1));
	--ion-button-secondary-border-disabled: 2px solid light-dark(var(--ion-color-cool-grey-c4), var(--ion-color-cool-grey-c3));
	--ion-button-secondary-text: light-dark(var(--ion-color-blue-b7), var(--ion-color-cool-grey-c1));
	--ion-button-secondary-text-hover: light-dark(#fff, var(--ion-color-blue-b7));
	--ion-button-secondary-text-active: light-dark(#fff, var(--ion-color-blue-b7));
	--ion-button-secondary-text-disabled: light-dark(var(--ion-color-cool-grey-c4), var(--ion-color-cool-grey-c3));

	/* ── IONOS-specific tokens (no NC equivalent) ─────────────────────────── */
	--ion-icon-filter-secondary: invert(8%) sepia(43%) saturate(7496%) hue-rotate(218deg) brightness(90%) contrast(91%);
	--ion-icon-filter-secondary-text-hover: brightness(0) saturate(100%) invert(100%) sepia(0%) saturate(7460%) hue-rotate(259deg) brightness(112%) contrast(100%);
	--ion-icon-filter-blue-b4: brightness(0) saturate(100%) invert(37%) sepia(26%) saturate(3654%) hue-rotate(185deg) brightness(88%) contrast(90%);
	--ion-icon-filter-blue-b5: brightness(0) saturate(100%) invert(21%) sepia(91%) saturate(6261%) hue-rotate(202deg) brightness(87%) contrast(93%);
	--ion-icon-filter-blue-b6: brightness(0) saturate(100%) invert(9%) sepia(91%) saturate(5873%) hue-rotate(206deg) brightness(85%) contrast(104%);
	--ion-icon-filter-blue-b8: brightness(0) saturate(100%) invert(10%) sepia(32%) saturate(3782%) hue-rotate(197deg) brightness(98%) contrast(106%);
	--ion-icon-filter-cool-grey-c1: brightness(0) saturate(100%) invert(100%) sepia(26%) saturate(874%) hue-rotate(176deg) brightness(100%) contrast(96%);

	--ion-breadcrumb-text-default: light-dark(var(--ion-color-secondary), var(--ion-color-cool-grey-c1));
	--ion-breadcrumb-text-hover: var(--ion-color-blue-b4);
	--ion-breadcrumb-text-active: var(--ion-color-blue-b5);
	--ion-breadcrumb-text-disabled: var(--ion-color-cool-grey-c4);

	--ion-files-list-background-hover: light-dark(var(--ion-color-blue-b1), var(--ion-color-cool-grey-c8));
	--ion-files-list-background-active: light-dark(var(--ion-color-blue-b2), var(--ion-color-cool-grey-c6));
	--ion-files-list-icon: var(--ion-color-blue-b4);

	--ion-context-menu-background: var(--color-main-background);
	--ion-context-menu-border: light-dark(var(--ion-color-blue-b4), var(--ion-color-cool-grey-c4));
	--ion-context-menu-item-background-hover: light-dark(var(--ion-color-blue-b1), var(--ion-color-cool-grey-c7));
	--ion-context-menu-item-background-active: light-dark(var(--ion-color-blue-b2), var(--ion-color-cool-grey-c6));
	--ion-context-menu-item-background-disabled: var(--ion-context-menu-background);
	--ion-context-menu-item-text: light-dark(var(--ion-color-secondary), var(--ion-color-cool-grey-c1));
	--ion-context-menu-item-text-disabled: light-dark(var(--ion-color-cool-grey-c4), var(--ion-color-cool-grey-c6));

	--ion-surface-dialog: light-dark(#fff, var(--ion-color-cool-grey-c8));

	--ion-dialog-filter-button-background: light-dark(var(--ion-color-cool-grey-c1), var(--ion-color-blue-b9));
	--ion-dialog-files-list-background: transparent;
	--ion-dialog-files-list-background-hover: light-dark(var(--ion-color-blue-b1), var(--ion-color-cool-grey-c7));

	--ion-chip-background: light-dark(var(--ion-color-blue-b4), var(--ion-color-cool-grey-c6));
	--ion-chip-text: #fff;

	--ion-dropdown-classic: var(--ion-color-cool-grey-c3);

	/* ── NC semantic overrides ────────────────────────────────────────────── */
	--color-main-background: var(--ion-color-main-background);
	--color-primary: var(--ion-color-primary);
	--color-primary-element: light-dark(var(--ion-color-blue-b7), var(--ion-color-blue-b4));
	--color-primary-element-hover: light-dark(var(--ion-color-blue-b4), var(--ion-color-blue-b5));
	--color-primary-element-light: light-dark(var(--ion-color-blue-b1), var(--ion-color-blue-b7));
	--color-primary-element-light-text: light-dark(var(--ion-color-cool-grey-c8), var(--ion-color-cool-grey-c1));
	--color-primary-element-light-hover: light-dark(var(--ion-color-cool-grey-c2), var(--ion-color-cool-grey-c7));
	--color-background-plain: light-dark(var(--ion-color-cool-grey-c1), var(--ion-color-cool-grey-c8));
	--color-background-hover: light-dark(var(--ion-color-blue-b1), var(--ion-color-cool-grey-c7));
	--color-main-text: light-dark(var(--ion-color-cool-grey-c7), var(--ion-color-cool-grey-c1));
	--color-text-maxcontrast: var(--color-main-text);
	--color-text-maxcontrast-default: var(--color-main-text);
	--color-text-maxcontrast-background-blur: var(--color-main-text);
	--color-text-light: var(--color-main-text);
	--color-text-lighter: var(--color-text-maxcontrast);
	--color-scrollbar: var(--color-main-text);
	--color-error: var(--ion-color-rose-r3);
	--color-warning: var(--ion-color-amber-y3);
	--color-success: var(--ion-color-green-g3);
	--color-info: var(--ion-color-sky-s3);
	--color-favorite: var(--ion-color-amber-y3);
	--color-shadow-header: light-dark(rgba(113, 128, 149, 0.5), rgba(113, 128, 149, 0.2));
	--default-font-size: 15px;
	--font-face: "Open sans", Arial, Helvetica, sans-serif;
	--background-invert-if-dark: no;
	--background-invert-if-bright: invert(100%);
	--background-image-invert-if-bright: no;
	--background-image-color-text: #ffffff;
}
```

- [ ] **Step 2: Verify no deleted tokens remain in variables.css**

Run:
```bash
grep -n "ion-button-primary\|ion-button-tertiary\|ion-button-sidebar\|ion-surface-primary\b\|ion-surface-secondary\b\|--ion-text:\|ion-color-typo-mild\|ion-button--icon-only" apps-custom/nc_theming/css/variables.css
```
Expected: no output.

---

## Task 2: Refactor `buttons.css`

Delete primary/tertiary color overrides. Replace `--ion-button--icon-only-*` and `--ion-color-typo-mild` with palette/NC semantic references. Replace `--ion-surface-secondary` with `--color-background-plain`. Replace `--ion-button-primary-*` in image editor section with NC semantics.

**Files:**
- Modify: `apps-custom/nc_theming/css/buttons.css`

- [ ] **Step 1: Write the new `buttons.css`**

Replace the entire file with:

```css
#body-user {
	.button-vue {
		border-radius: 30px;

		&.button-vue--icon-and-text {
			min-height: 36px;
			min-width: 98px;
			padding: 0 16px;

			.button-vue__wrapper {
				height: 36px;
				gap: 8px;

				.button-vue__text {
					font-size: 14px;
					font-weight: 600;
				}

				.button-vue__icon, .button-vue__icon span[role=img] {
					min-height: 16px;
					min-width: auto;
					width: auto;
				}
			}

			&.button-vue--vue-primary[aria-label*="Copy"],
			&.button-vue--vue-secondary, &.button-vue--vue-tertiary[aria-label="Cancel"] {
				background-color: var(--ion-button-secondary-background-default);
				border: var(--ion-button-secondary-border-default);

				&:not(.action-item__menutoggle) {
					.button-vue__text, .button-vue__icon svg {
						color: var(--ion-button-secondary-text);
					}
				}

				&:hover:not(:disabled):not(.button-vue--disabled) {
					background-color: var(--ion-button-secondary-background-hover);
					border-color: var(--ion-button-secondary-background-hover);

					&:not(.action-item--single) {
						.button-vue__text, .button-vue__icon svg {
							color: var(--ion-button-secondary-text-hover);
						}
					}
				}

				&:active:not(:disabled):not(.button-vue--disabled) {
					background-color: var(--ion-button-secondary-background-active);

					&:not(.action-item--single) {
						.button-vue__text, .button-vue__icon svg {
							color: var(--ion-button-secondary-text-active);
						}
					}
				}
			}

			/* NC drives primary via --color-primary-element */
			/* NC drives tertiary via --color-primary-element-light */

			.action-item {
				.button-vue__text, .button-vue__icon svg {
					color: var(--ion-color-blue-b4);
				}

				&:hover {
					.button-vue__text, .button-vue__icon svg {
						color: var(--ion-color-blue-b5);
					}
				}

				&:active {
					.button-vue__text, .button-vue__icon svg {
						color: var(--ion-color-primary);
					}
				}
			}

			/* mobile and tablet */
			@media only screen and (max-width: 1023px) {
				min-height: 48px;
			}
		}

		&.button-vue--text-only {
			&.button-vue--vue-secondary, &.button-vue--vue-tertiary[aria-label="Cancel"] {
				background-color: var(--ion-button-secondary-background-default);
				border: var(--ion-button-secondary-border-default);

				.button-vue__text {
					color: var(--ion-button-secondary-text);
				}

				&:hover:not(:disabled):not(.button-vue--disabled) {
					background-color: var(--ion-button-secondary-background-hover);
					border-color: var(--ion-button-secondary-background-hover);

					.button-vue__text {
						color: var(--ion-button-secondary-text-hover);
					}
				}

				&:active:not(:disabled):not(.button-vue--disabled) {
					background-color: var(--ion-button-secondary-background-active);

					.button-vue__text {
						color: var(--ion-button-secondary-text-active);
					}
				}
			}

			/* mobile and tablet */
			@media only screen and (max-width: 1023px) {
				min-height: 48px;
			}
		}

		&.button-vue--icon-only,
		.button-vue__icon {
			height: 32px !important;
			width: 32px !important;
			min-width: 32px;
			min-height: 32px;
			margin: auto 0;
			&.button-vue--vue-tertiary {
				.button-vue__icon span[role=img]>svg {
					color: light-dark(var(--ion-color-blue-b4), var(--ion-color-cool-grey-c3));
				}

				&[class^="files-list__header-"], &.app-navigation-toggle {
					.button-vue__icon span[role=img]>svg {
						color: var(--color-main-text);
					}
				}

				&:hover:not(:disabled):not(.button-vue--disabled):not(&.modal-container__close):not(.icon-collapse):not(.files-list__header-grid-button) {
					background-color: var(--ion-color-blue-b4);

					.button-vue__icon span[role=img]>svg {
						color: #fff;
					}
				}

				&:active:not(:disabled):not(.button-vue--disabled):not(&.modal-container__close):not(.icon-collapse):not(.files-list__header-grid-button) {
					background-color: light-dark(var(--ion-color-blue-b6), var(--ion-color-blue-b3));

					.button-vue__icon span[role=img]>svg {
						color: #fff;
					}
				}

				&.files-list__header-grid-button {
					&:hover {
						background-color: transparent;
						.button-vue__icon span[role=img]>svg {
							color: var(--ion-color-blue-b5);
						}
					}
					&:active {
						background-color: transparent;
						.button-vue__icon span[role=img]>svg {
							color: var(--color-main-text);
						}
					}
				}
				&.modal-container__close {
					margin: 4px;
					.button-vue__icon span[role=img]>svg {
						width: 24px;
						height: 24px;
						color: light-dark(var(--ion-color-cool-grey-c5), var(--ion-color-cool-grey-c1));
					}

					&:hover {
						background-color: transparent;

						.button-vue__icon span[role=img]>svg {
							color: light-dark(var(--ion-color-cool-grey-c6), var(--ion-color-cool-grey-c4));
						}
					}

					&:active {
						background-color: transparent;

						.button-vue__icon span[role=img]>svg {
							color: var(--color-main-text);
						}
					}
				}
			}
		}
	}

	/* files list checkbox buttons */
	.checkbox-radio-switch {
		.checkbox-content__icon svg {
			color: var(--ion-color-cool-grey-c5);
		}

		:not(span[data-cy-files-sharing-share-permissions-bundle])&:hover {
			.checkbox-content {
				background: transparent;
			}

			.checkbox-content__icon svg {
				color: var(--ion-color-blue-b4);
			}
		}

		.checkbox-content__icon--checked svg, &.checkbox-radio-switch--indeterminate svg {
			color: var(--ion-color-blue-b4);
		}
	}

	/* files list column sorting buttons */
	.files-list, .file-picker__files {
		table th.files-list__column, th[class^="row-"] {
			.button-vue--vue-tertiary {
				padding: 0 12px;

				.button-vue__text, .files-list__column-sort-button-text {
					color: var(--color-main-text);
					font-weight: 600;
				}
				.button-vue__icon {
					flex: 1;
					min-width: 16px;
					max-width: max-content;

					span[role=img]>svg {
						color: var(--color-main-text);
					}
				}

				&:hover {
					background: transparent;

					.button-vue__text, .files-list__column-sort-button-text, span[role=img]>svg {
						color: var(--ion-color-blue-b5);
					}
				}

				&:active {
					background: transparent;

					.files-list__column-sort-button-text, span[role=img]>svg {
						color: var(--ion-color-primary);
					}
				}
			}

			&.files-list__row-size span.button-vue__wrapper {
				flex-direction: row-reverse;
			}
		}
	}

	.files-list__breadcrumbs, .breadcrumb__actions {
		align-items: center;
		gap: 8px;
	}

	/* drop down chevron-up and down icon */
	.vs__open-indicator-button svg{
		height: 24px;
		width: 24px;
	}
}

#body-user, #body-public {
	div.modal-mask .modal-header {
		.modal-header__name {
			color: var(--background-image-color-text);
		}
		div.icons-menu {
			button, .header-actions {
				span[role=img]>svg {
					color: var(--background-image-color-text);
				}
			}
		}
	}

	button.toast-close {
		@media (prefers-color-scheme: dark) {
			background-color: transparent;
			filter: var(--ion-icon-filter-cool-grey-c1);
		}
	}

	/* share settings icons */
	.avatar-external.icon-external-white {
		background-size: 16px;
	}
	.avatar-class-icon.avatar-link-share.icon-public-white {
		background-size: 18px;
		@media (prefers-color-scheme: dark) {
			background-image: var(--original-icon-public-white);
		}
	}
	.avatar-shared.icon-more-white {
		background-size: 18px;
	}

	/* image editor buttons */
	.viewer__image-editor, div#SfxPopper, div.SfxModal-Wrapper {
		@media (prefers-color-scheme: dark) {
			div.FIE_tab, .FIE_tools-items div, .SfxPopper-root div, .SfxSelect-root {
				background-color: var(--color-background-plain);

				&[aria-selected="true"] {
					border: var(--ion-button-secondary-border-default);
					box-shadow: none;
				}

				svg {
					color: var(--color-main-text);
				}
			}

			button, .SfxMenuItem-root {
				background-color: var(--color-background-plain);
				color: var(--ion-button-secondary-text);

				&:disabled {
					background-color: transparent !important;
				}

				&:hover {
					background-color: var(--color-background-hover);

					&:not(:disabled), svg, .SfxSelect-tickIcon, &::before {
						color: var(--color-main-text);
					}
				}

				&[color="primary"] {
					background-color: var(--color-primary-element) !important;

					&:hover {
						background-color: var(--color-primary-element-hover) !important;
					}
				}
			}

			.SfxInput-root:not(.SfxSelect-root) {
				background-color: transparent;
				border: none;
			}
		}

		.FIE_topbar-buttons-wrapper {
			gap: 12px;
		}

		.SfxCrossButton-root svg path {
			transform: scale(1);
		}
	}
}

#body-login {
	a.button:hover {
		background-color: var(--color-primary-element-light-hover);
		color: var(--color-primary-element-light-text);
	}
	a.button:focus {
		background-color: var(--color-main-background);
	}
}
```

- [ ] **Step 2: Verify no deleted tokens remain in buttons.css**

Run:
```bash
grep -n "ion-button-primary\|ion-button-tertiary\|ion-button-sidebar\|ion-surface-secondary\|ion-color-typo-mild\|ion-button--icon-only" apps-custom/nc_theming/css/buttons.css
```
Expected: no output.

---

## Task 3: Refactor `navigation.css`

Replace all `--ion-button-sidebar-*` and `--ion-surface-*` references with NC semantic variables.

**Files:**
- Modify: `apps-custom/nc_theming/css/navigation.css`

- [ ] **Step 1: Write the new `navigation.css`**

Replace the entire file with:

```css
#body-user {
	.app-navigation__search {
		display: none;
	}

	.app-navigation-list {
		padding: 20px;
	}

	div[data-cy-files-navigation].app-navigation:has(nav#app-navigation-vue) {
		background-color: var(--color-background-plain);
		.app-navigation-entry {
			span[role=img]>svg {
				color: var(--color-main-text);
				height: 16px;
				width: auto;
			}
			.app-navigation-entry__name {
				font-weight: 600;
				color: var(--color-main-text) !important;
			}
			border-radius: var(--border-radius-pill);
			&.active {
				background-color: var(--color-primary-element) !important;
				.app-navigation-entry-link {
					color: var(--color-main-text) !important;
				}
			}
			&:hover {
				background-color: var(--color-background-hover);
			}

			/* fix Storage Quota color after click */
			&:focus-within:not(:active) {
				background-color: var(--color-background-plain);
			}
		}

		.app-navigation-entry--opened:has(.app-navigation-entry__children) {
			background: var(--color-main-background);
			border-radius: 8px;
			.app-navigation-entry__children {
				padding: 0 8px;
				gap: 0;
				.app-navigation-entry {
					border-radius: 4px;
				}
				.app-navigation-entry-wrapper {
					background: var(--color-main-background);
					border-radius: 8px;
					min-height: 40px;
					margin: 4px 0;
				}
			}
		}

		/* Navigation Toggle Button */
		div.app-navigation-toggle-wrapper button.app-navigation-toggle.button-vue--icon-only {
			margin: 18px 0;
			background-color: var(--color-background-plain) !important;
			@media only screen and (max-width: 1023px) {
				margin: 12px 0;
			}
			span[role=img]>svg {
				color: var(--color-main-text) !important;
			}
			&:hover {
				background-color: var(--color-background-hover) !important;
				span[role=img]>svg {
					color: var(--color-main-text) !important;
				}
			}
		}

		/* Sub Navigation Toggle Icon Button */
		.button-vue--icon-only:not(.app-navigation-toggle) {
			margin: 6px;
			background-color: transparent;
			span[role=img]>svg {
				height: 16px;
				width: 16px;
				color: var(--color-main-text);
			}
			&:hover {
				background-color: transparent;
				span[role=img]>svg {
					color: var(--color-main-text);
				}
			}
		}

		@media only screen and (max-width: 767px) {
			&:has(span.menu-open-icon) {
				width: auto;
				max-width: inherit;

				.app-navigation-list {
					padding-right: calc(var(--default-clickable-area) + 16px);
				}

				div.app-navigation-toggle-wrapper {
					margin-inline-end: calc(var(--default-clickable-area) - 24px);
				}
			}
		}
	}
}
```

- [ ] **Step 2: Verify no old token references remain in navigation.css**

Run:
```bash
grep -n "ion-button-sidebar\|ion-surface-primary\|ion-surface-secondary" apps-custom/nc_theming/css/navigation.css
```
Expected: no output.

---

## Task 4: Final verification and commit

- [ ] **Step 1: Verify no deleted tokens referenced anywhere in the nc_theming CSS**

Run:
```bash
grep -rn "ion-button-primary\|ion-button-tertiary\|ion-button-sidebar\|ion-surface-primary\b\|ion-surface-secondary\b\|--ion-text:\|ion-color-typo-mild\|ion-button--icon-only" apps-custom/nc_theming/css/
```
Expected: no output.

- [ ] **Step 2: Clear NC theme cache**

Run (from repo root):
```bash
php occ maintenance:repair
```

- [ ] **Step 3: Visual check in browser**

Hard-refresh (Ctrl+Shift+R) and verify:
1. Primary buttons: dark navy (`#0B2A63`) background in light mode
2. Secondary buttons: transparent with dark-navy outline border
3. Tertiary buttons: light blue (`#dbedf8`) background in light mode
4. Sidebar: cool-grey-c1 (`#f4f7fa`) background in light mode
5. Nav active item: dark navy background
6. Nav hover: light blue (`#dbedf8`) background

- [ ] **Step 4: Commit submodule**

```bash
cd apps-custom/nc_theming
git add css/variables.css css/buttons.css css/navigation.css
git commit -s -m "refactor(theming): pure NC semantics — drop intermediate token layer

NC semantic variables now drive all standard components. Custom CSS only
for the IONOS outlined secondary button which has no NC equivalent.
Removes --ion-button-primary-*, --ion-button-tertiary-*,
--ion-button-sidebar-*, --ion-surface-*, --ion-button--icon-only-*
token groups from variables.css."
```

- [ ] **Step 5: Update nc-server submodule pointer**

```bash
cd ../..
git add apps-custom/nc_theming
git commit -s -m "IONOS(nc_theming): update submodule — pure NC semantics theming refactor"
```
