<!--
 - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSettingsSection :name="t('federatedfilesharing', 'Federated Cloud Sharing')"
		:description="t('federatedfilesharing', 'Adjust how people can share between servers. This includes shares between people on this server as well if they are using federated sharing.')"
		:doc-url="sharingFederatedDocUrl">
		<NcCheckboxRadioSwitch type="switch"
			:checked.sync="outgoingServer2serverShareEnabled"
			@update:checked="update('outgoing_server2server_share_enabled', outgoingServer2serverShareEnabled)">
			{{ t('federatedfilesharing', 'Allow people on this server to send shares to other servers (this option also allows WebDAV access to public shares)') }}
		</NcCheckboxRadioSwitch>

		<NcCheckboxRadioSwitch type="switch"
			:checked.sync="incomingServer2serverShareEnabled"
			@update:checked="update('incoming_server2server_share_enabled', incomingServer2serverShareEnabled)">
			{{ t('federatedfilesharing', 'Allow people on this server to receive shares from other servers') }}
		</NcCheckboxRadioSwitch>

		<NcCheckboxRadioSwitch v-if="federatedGroupSharingSupported"
			type="switch"
			:checked.sync="outgoingServer2serverGroupShareEnabled"
			@update:checked="update('outgoing_server2server_group_share_enabled', outgoingServer2serverGroupShareEnabled)">
			{{ t('federatedfilesharing', 'Allow people on this server to send shares to groups on other servers') }}
		</NcCheckboxRadioSwitch>

		<NcCheckboxRadioSwitch v-if="federatedGroupSharingSupported"
			type="switch"
			:checked.sync="incomingServer2serverGroupShareEnabled"
			@update:checked="update('incoming_server2server_group_share_enabled', incomingServer2serverGroupShareEnabled)">
			{{ t('federatedfilesharing', 'Allow people on this server to receive group shares from other servers') }}
		</NcCheckboxRadioSwitch>

		<fieldset>
			<legend>{{ t('federatedfilesharing', 'The lookup server is only available for global scale.') }}</legend>

			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="lookupServerEnabled"
				disabled
				@update:checked="update('lookupServerEnabled', lookupServerEnabled)">
				{{ t('federatedfilesharing', 'Search global and public address book for people') }}
			</NcCheckboxRadioSwitch>

			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="lookupServerUploadEnabled"
				disabled
				@update:checked="update('lookupServerUploadEnabled', lookupServerUploadEnabled)">
				{{ t('federatedfilesharing', 'Allow people to publish their data to a global and public address book') }}
			</NcCheckboxRadioSwitch>
		</fieldset>
	</NcSettingsSection>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { showError } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'
import { confirmPassword } from '@nextcloud/password-confirmation'
import axios from '@nextcloud/axios'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'

import '@nextcloud/password-confirmation/dist/style.css'

export default {
	name: 'AdminSettings',

	components: {
		NcCheckboxRadioSwitch,
		NcSettingsSection,
	},

	data() {
		return {
			outgoingServer2serverShareEnabled: loadState('federatedfilesharing', 'outgoingServer2serverShareEnabled'),
			incomingServer2serverShareEnabled: loadState('federatedfilesharing', 'incomingServer2serverShareEnabled'),
			outgoingServer2serverGroupShareEnabled: loadState('federatedfilesharing', 'outgoingServer2serverGroupShareEnabled'),
			incomingServer2serverGroupShareEnabled: loadState('federatedfilesharing', 'incomingServer2serverGroupShareEnabled'),
			federatedGroupSharingSupported: loadState('federatedfilesharing', 'federatedGroupSharingSupported'),
			lookupServerEnabled: loadState('federatedfilesharing', 'lookupServerEnabled'),
			lookupServerUploadEnabled: loadState('federatedfilesharing', 'lookupServerUploadEnabled'),
			internalOnly: loadState('federatedfilesharing', 'internalOnly'),
			sharingFederatedDocUrl: loadState('federatedfilesharing', 'sharingFederatedDocUrl'),
		}
	},
	methods: {
		async update(key, value) {
			await confirmPassword()

			const url = generateOcsUrl('/apps/provisioning_api/api/v1/config/apps/{appId}/{key}', {
				appId: 'files_sharing',
				key,
			})

			const stringValue = value ? 'yes' : 'no'
			try {
				const { data } = await axios.post(url, {
					value: stringValue,
				})
				this.handleResponse({
					status: data.ocs?.meta?.status,
				})
			} catch (e) {
				this.handleResponse({
					errorMessage: t('federatedfilesharing', 'Unable to update federated files sharing config'),
					error: e,
				})
			}
		},
		async handleResponse({ status, errorMessage, error }) {
			if (status !== 'ok') {
				showError(errorMessage)
				console.error(errorMessage, error)
			}
		},
	},
}
</script>
