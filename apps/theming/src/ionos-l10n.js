/**
 * SPDX-FileCopyrightText: 2026 STRATO AG
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// IONOS-only translations for the 'files' app (SidebarQuota).
// Kept in apps/theming/l10n-ionos/<lang>.json instead of apps/files/l10n so that Transifex syncs
// and upstream merges never touch them. Registration merges into the existing app bundle, so
// upstream strings are unaffected. Add a language by dropping in another <lang>.json.
import { getLanguage, register } from '@nextcloud/l10n'

const TRANSLATIONS = Object.fromEntries(
	Object.entries(import.meta.glob('../l10n-ionos/*.json', { eager: true, import: 'default' }))
		.map(([path, translations]) => [path.match(/([^/]+)\.json$/)[1], translations]),
)

const language = getLanguage().replace('-', '_')
const translations = TRANSLATIONS[language]
	?? TRANSLATIONS[Object.keys(TRANSLATIONS).find((key) => key.split('_')[0] === language.split('_')[0])]

if (translations) {
	register('files', translations, (n) => (n !== 1 ? 1 : 0))
}
