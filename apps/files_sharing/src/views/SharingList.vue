<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<ul class="sharing-sharee-list">
		<SharingEntry v-for="share in shares"
			:key="share.id"
			:file-info="fileInfo"
			:share="share"
			:is-unique="isUnique(share)"
			@open-sharing-details="openSharingDetails(share)" />
	</ul>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import SharingEntry from '../components/SharingEntry.vue'
import ShareDetails from '../mixins/ShareDetails.js'
import { ShareType } from '@nextcloud/sharing'

export default {
	name: 'SharingList',

	components: {
		SharingEntry,
	},

	mixins: [ShareDetails],

	props: {
		fileInfo: {
			type: Object,
			default: () => { },
			required: true,
		},
		shares: {
			type: Array,
			default: () => [],
			required: true,
		},
	},
	computed: {
		hasShares() {
			return this.shares.length === 0
		},
		isUnique() {
			return (share) => {
				return [...this.shares].filter((item) => {
					return share.type === ShareType.User && share.shareWithDisplayName === item.shareWithDisplayName
				}).length <= 1
			}
		},
	},
}
</script>
