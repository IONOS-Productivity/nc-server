/*!
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import type { User } from '@nextcloud/cypress'
import { join } from 'path'
import { getRowForFileId } from './FilesUtils.ts'

/**
 * Check that the sidebar is opened for a specific file
 * @param name The name of the file
 */
function sidebarIsOpen(name: string): void {
	cy.get('[data-cy-sidebar]')
		.should('be.visible')
		.findByRole('heading', { name })
		.should('be.visible')
}

/**
 * Skip a test without viewer installed
 */
function skipIfViewerDisabled(this: Mocha.Context): void {
	cy.runOccCommand('app:list --enabled --output json')
		.then((exec) => exec.stdout)
		.then((output) => JSON.parse(output))
		.then((obj) => 'viewer' in obj.enabled)
		.then((enabled) => {
			if (!enabled) {
				this.skip()
			}
		})
}

describe('Check router query flags:', function() {
	let user: User
	let imageId: number
	let archiveId: number
	let folderId: number

	before(() => {
		cy.createRandomUser().then(($user) => {
			user = $user
			cy.uploadFile(user, 'image.jpg')
				.then((response) => { imageId = Number.parseInt(response.headers['oc-fileid']) })
			cy.mkdir(user, '/folder')
				.then((response) => { folderId = Number.parseInt(response.headers['oc-fileid']) })
			cy.uploadContent(user, new Blob([]), 'application/zstd', '/archive.zst')
				.then((response) => { archiveId = Number.parseInt(response.headers['oc-fileid']) })
			cy.login(user)
		})
	})

	describe('"openfile"', function() {
		/** Check the viewer is open and shows the image */
		function viewerShowsImage(): void {
			cy.findByRole('dialog', { name: 'image.jpg' })
				.should('be.visible')
				.find(`img[src*="fileId=${imageId}"]`)
				.should('be.visible')
		}

		it('opens files with default action', function() {
			skipIfViewerDisabled.call(this)

			cy.visit(`/apps/files/files/${imageId}?openfile`)
			viewerShowsImage()
		})

		it('opens files with default action using explicit query state', function() {
			skipIfViewerDisabled.call(this)

			cy.visit(`/apps/files/files/${imageId}?openfile=true`)
			viewerShowsImage()
		})

		it('does not open files with default action when using explicitly query value `false`', function() {
			skipIfViewerDisabled.call(this)

			cy.visit(`/apps/files/files/${imageId}?openfile=false`)
			getRowForFileId(imageId)
				.should('be.visible')
				.and('have.class', 'files-list__row--active')

			cy.findByRole('dialog', { name: 'image.jpg' })
				.should('not.exist')
		})

		it('does not open folders but shows details', () => {
			cy.visit(`/apps/files/files/${folderId}?openfile`)

			// See the sidebar is correctly opened
			sidebarIsOpen('folder')

			// see the folder was not changed
			getRowForFileId(imageId).should('exist')
		})

		it('does not open unknown file types but shows details', () => {
			cy.visit(`/apps/files/files/${archiveId}?openfile`)

			// See the sidebar is correctly opened
			sidebarIsOpen('archive.zst')

			// See no file was downloaded
			const downloadsFolder = Cypress.config('downloadsFolder')
			cy.readFile(join(downloadsFolder, 'archive.zst')).should('not.exist')
		})
	})
})
