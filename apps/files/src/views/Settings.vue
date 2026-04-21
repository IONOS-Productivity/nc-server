<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

		<!-- Settings API-->
		<NcAppSettingsSection v-if="settings.length !== 0"
			id="more-settings"
			:name="t('files', 'Additional settings')">
			<template v-for="setting in settings">
				<Setting :key="setting.name" :el="setting.el" />
			</template>
		</NcAppSettingsSection>

		<!-- Webdav URL-->
		<NcAppSettingsSection id="webdav" :name="t('files', 'WebDAV')">
			<NcInputField id="webdav-url-input"
				:label="t('files', 'WebDAV URL')"
				:show-trailing-button="true"
				:success="webdavUrlCopied"
				:trailing-button-label="t('files', 'Copy to clipboard')"
				:value="webdavUrl"
				readonly="readonly"
				type="url"
				@focus="$event.target.select()"
				@trailing-button-click="copyCloudId">
				<template #trailing-button-icon>
					<Clipboard :size="20" />
				</template>
			</NcInputField>
			<em>
				<a class="setting-link"
					:href="webdavDocs"
					target="_blank"
					rel="noreferrer noopener">
					{{ t('files', 'Use this address to access your Files via WebDAV') }} ↗
				</a>
			</em>
			<br>
			<em>
				<a class="setting-link" :href="appPasswordUrl">
					{{ t('files', 'If you have enabled 2FA, you must create and use a new app password by clicking here.') }} ↗
				</a>
			</em>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="warning" :name="t('files', 'Warnings')">
			<em>{{ t('files', 'Prevent warning dialogs from open or reenable them.') }}</em>
			<NcCheckboxRadioSwitch type="switch"
				:checked="userConfig.show_dialog_file_extension"
				@update:checked="setConfig('show_dialog_file_extension', $event)">
				{{ t('files', 'Show a warning dialog when changing a file extension.') }}
			</NcCheckboxRadioSwitch>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="shortcuts"
			:name="t('files', 'Keyboard shortcuts')">
			<em>{{ t('files', 'Speed up your Files experience with these quick shortcuts.') }}</em>

			<h3>{{ t('files', 'Actions') }}</h3>
			<dl>
				<div>
					<dt class="shortcut-key">
						<kbd>a</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Open the actions menu for a file') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>F2</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Rename a file') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>Del</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Delete a file') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>s</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Favorite or remove a file from favorites') }}
					</dd>
				</div>
				<div v-if="isSystemtagsEnabled">
					<dt class="shortcut-key">
						<kbd>t</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Manage tags for a file') }}
					</dd>
				</div>
			</dl>

			<h3>{{ t('files', 'Selection') }}</h3>
			<dl>
				<div>
					<dt class="shortcut-key">
						<kbd>Ctrl</kbd> + <kbd>A</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Select all files') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>ESC</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Deselect all files') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>Ctrl</kbd> + <kbd>Space</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Select or deselect a file') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>Ctrl</kbd> + <kbd>Shift</kbd> <span>+ <kbd>Space</kbd></span>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Select a range of files') }}
					</dd>
				</div>
			</dl>

			<h3>{{ t('files', 'Navigation') }}</h3>
			<dl>
				<div>
					<dt class="shortcut-key">
						<kbd>Alt</kbd> + <kbd>↑</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Navigate to the parent folder') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>↑</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Navigate to the file above') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>↓</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Navigate to the file below') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>←</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Navigate to the file on the left (in grid mode)') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>→</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Navigate to the file on the right (in grid mode)') }}
					</dd>
				</div>
			</dl>

			<h3>{{ t('files', 'View') }}</h3>
			<dl>
				<div>
					<dt class="shortcut-key">
						<kbd>V</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Toggle the grid view') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>D</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Open the sidebar for a file') }}
					</dd>
				</div>
				<div>
					<dt class="shortcut-key">
						<kbd>?</kbd>
					</dt>
					<dd class="shortcut-description">
						{{ t('files', 'Show those shortcuts') }}
					</dd>
				</div>
			</dl>
		</NcAppSettingsSection>
	</NcAppSettingsDialog>
</template>

<script>
import { getCapabilities } from '@nextcloud/capabilities'
import Clipboard from 'vue-material-design-icons/ContentCopy.vue'
import NcAppSettingsDialog from '@nextcloud/vue/dist/Components/NcAppSettingsDialog.js'
import NcAppSettingsSection from '@nextcloud/vue/dist/Components/NcAppSettingsSection.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'

import { generateRemoteUrl, generateUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { nextTick } from 'vue'
import NcAppSettingsDialog from '@nextcloud/vue/components/NcAppSettingsDialog'
import FilesAppSettingsAppearance from '../components/FilesAppSettings/FilesAppSettingsAppearance.vue'
import FilesAppSettingsGeneral from '../components/FilesAppSettings/FilesAppSettingsGeneral.vue'
import FilesAppSettingsLegacyApi from '../components/FilesAppSettings/FilesAppSettingsLegacyApi.vue'
import FilesAppSettingsShortcuts from '../components/FilesAppSettings/FilesAppSettingsShortcuts.vue'
import FilesAppSettingsWarnings from '../components/FilesAppSettings/FilesAppSettingsWarnings.vue'
import FilesAppSettingsWebDav from '../components/FilesAppSettings/FilesAppSettingsWebDav.vue'

defineProps<{
	open: boolean
}>()

const emit = defineEmits<{
	(e: 'close'): void
	(e: 'update:open', open: boolean): void
}>()

// ? opens the settings dialog on the keyboard shortcuts section
useHotKey('?', showKeyboardShortcuts, {
	stop: true,
	prevent: true,
})

/**
 * Opens the settings dialog and scrolls to the keyboard shortcuts section
 */
async function showKeyboardShortcuts() {
	emit('update:open', true)

	await nextTick()
	document.getElementById('settings-section_keyboard-shortcuts')!.scrollIntoView({
		behavior: 'smooth',
		inline: 'nearest',
	})
}
</script>

<template>
	<NcAppSettingsDialog :legacy="false"
		:name="t('files', 'Files settings')"
		no-version
		:open="open"
		show-navigation
		@update:open="emit('close')">
		<FilesAppSettingsGeneral />
		<FilesAppSettingsAppearance />
		<FilesAppSettingsLegacyApi />
		<FilesAppSettingsWarnings />
		<FilesAppSettingsWebDav />
		<FilesAppSettingsShortcuts />
	</NcAppSettingsDialog>
</template>
