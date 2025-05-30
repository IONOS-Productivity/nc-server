/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { emit } from '@nextcloud/event-bus'
import { generateUrl } from '@nextcloud/router'
import { Permission, type Node, View, FileAction } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import axios from '@nextcloud/axios'
import PQueue from 'p-queue'
import Vue from 'vue'

import StarOutlineSvg from '@mdi/svg/svg/star-outline.svg?raw'
import StarSvg from '@mdi/svg/svg/star.svg?raw'

import logger from '../logger.ts'
import { encodePath } from '@nextcloud/paths'

const queue = new PQueue({ concurrency: 5 })

// If any of the nodes is not favorited, we display the favorite action.
const shouldFavorite = (nodes: Node[]): boolean => {
	return nodes.some(node => node.attributes.favorite !== 1)
}

export const favoriteNode = async (node: Node, view: View, willFavorite: boolean): Promise<boolean> => {
	try {
		// TODO: migrate to webdav tags plugin
		const url = generateUrl('/apps/files/api/v1/files') + encodePath(node.path)
		await axios.post(url, {
			tags: willFavorite
				? [window.OC.TAG_FAVORITE]
				: [],
		})

		// Let's delete if we are in the favourites view
		// AND if it is removed from the user favorites
		// AND it's in the root of the favorites view
		if (view.id === 'favorites' && !willFavorite && node.dirname === '/') {
			emit('files:node:deleted', node)
		}

		// Update the node webdav attribute
		Vue.set(node.attributes, 'favorite', willFavorite ? 1 : 0)

		// Dispatch event to whoever is interested
		if (willFavorite) {
			emit('files:favorites:added', node)
		} else {
			emit('files:favorites:removed', node)
		}

		return true
	} catch (error) {
		const action = willFavorite ? 'adding a file to favourites' : 'removing a file from favourites'
		logger.error('Error while ' + action, { error, source: node.source, node })
		return false
	}
}

export const action = new FileAction({
	id: 'favorite',
	displayName(nodes: Node[]) {
		return shouldFavorite(nodes)
			? t('files', 'Add to favorites')
			: t('files', 'Remove from favorites')
	},
	iconSvgInline: (nodes: Node[]) => {
		return shouldFavorite(nodes)
			? StarOutlineSvg
			: StarSvg
	},

	enabled(nodes: Node[]) {
		// We can only favorite nodes within files and with permissions
		return !nodes.some(node => !node.root?.startsWith?.('/files'))
			&& nodes.every(node => node.permissions !== Permission.NONE)
	},

	async exec(node: Node, view: View) {
		const willFavorite = shouldFavorite([node])
		return await favoriteNode(node, view, willFavorite)
	},
	async execBatch(nodes: Node[], view: View) {
		const willFavorite = shouldFavorite(nodes)

		// Map each node to a promise that resolves with the result of exec(node)
		const promises = nodes.map(node => {
			// Create a promise that resolves with the result of exec(node)
			const promise = new Promise<boolean>(resolve => {
				queue.add(async () => {
					try {
						await favoriteNode(node, view, willFavorite)
						resolve(true)
					} catch (error) {
						logger.error('Error while adding file to favorite', { error, source: node.source, node })
						resolve(false)
					}
				})
			})
			return promise
		})

		return Promise.all(promises)
	},

	order: -50,
})
