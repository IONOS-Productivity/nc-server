/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
// eslint-disable-next-line n/no-extraneous-import
import axios, { type AxiosResponse } from 'axios'
import { addCommands, User } from '@nextcloud/cypress'
import { basename } from 'path'

// Add custom commands
import '@testing-library/cypress/add-commands'
import 'cypress-if'
import 'cypress-wait-until'
addCommands()

// Register this file's custom commands types
declare global {
	// eslint-disable-next-line @typescript-eslint/no-namespace
	namespace Cypress {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars
		interface Chainable<Subject = any> {
			/**
			 * Enable or disable a given user
			 */
			// eslint-disable-next-line @typescript-eslint/no-explicit-any
			enableUser(user: User, enable?: boolean): Cypress.Chainable<Cypress.Response<any>>,

			/**
			 * Upload a file from the fixtures folder to a given user storage.
			 * **Warning**: Using this function will reset the previous session
			 */
			uploadFile(user: User, fixture?: string, mimeType?: string, target?: string): Cypress.Chainable<void>,

			/**
			 * Upload a raw content to a given user storage.
			 * **Warning**: Using this function will reset the previous session
			 */
			uploadContent(user: User, content: Blob, mimeType: string, target: string, mtime?: number): Cypress.Chainable<AxiosResponse>,

			/**
			 * Create a new directory
			 * **Warning**: Using this function will reset the previous session
			 */
			mkdir(user: User, target: string): Cypress.Chainable<void>,

			/**
			 * Set a file as favorite (or remove from favorite)
			 */
			setFileAsFavorite(user: User, target: string, favorite?: boolean): Cypress.Chainable<void>,

			/**
			 * Reset the admin theming entirely.
			 * **Warning**: Using this function will reset the previous session
			 */
			resetAdminTheming(): Cypress.Chainable<void>,

			/**
			 * Reset the user theming settings.
			 * If provided, will clear session and login as the given user.
			 * **Warning**:  Providing a user will reset the previous session.
			 */
			resetUserTheming(user?: User): Cypress.Chainable<void>,
		}
	}
}

const url = (Cypress.config('baseUrl') || '').replace(/\/index.php\/?$/g, '')
Cypress.env('baseUrl', url)

/**
 * Enable or disable a user
 * TODO: standardise in @nextcloud/cypress
 *
 * @param {User} user the user to dis- / enable
 * @param {boolean} enable True if the user should be enable, false to disable
 */
Cypress.Commands.add('enableUser', (user: User, enable = true) => {
	const url = `${Cypress.config('baseUrl')}/ocs/v2.php/cloud/users/${user.userId}/${enable ? 'enable' : 'disable'}`.replace('index.php/', '')
	return cy.request({
		method: 'PUT',
		url,
		form: true,
		auth: {
			user: 'admin',
			password: 'admin',
		},
		headers: {
			'OCS-ApiRequest': 'true',
			'Content-Type': 'application/x-www-form-urlencoded',
		},
	}).then((response) => {
		cy.log(`Enabled user ${user}`, response.status)
		return cy.wrap(response)
	})
})

/**
 * cy.uploadedFile - uploads a file from the fixtures folder
 * TODO: standardise in @nextcloud/cypress
 *
 * @param {User} user the owner of the file, e.g. admin
 * @param {string} fixture the fixture file name, e.g. image1.jpg
 * @param {string} mimeType e.g. image/png
 * @param {string} [target] the target of the file relative to the user root
 */
Cypress.Commands.add('uploadFile', (user, fixture = 'image.jpg', mimeType = 'image/jpeg', target = `/${fixture}`) => {
	// get fixture
	return cy.fixture(fixture, 'base64')
		.then((file) => (
			// convert the base64 string to a blob
			Cypress.Blob.base64StringToBlob(file, mimeType)
		))
		.then((blob) => cy.uploadContent(user, blob, mimeType, target))
})

Cypress.Commands.add('setFileAsFavorite', (user: User, target: string, favorite = true) => {
	// eslint-disable-next-line cypress/unsafe-to-chain-command
	cy.clearAllCookies()
		.then(async () => {
			try {
				const rootPath = `${Cypress.env('baseUrl')}/remote.php/dav/files/${encodeURIComponent(user.userId)}`
				const filePath = target.split('/').map(encodeURIComponent).join('/')
				const response = await axios({
					url: `${rootPath}${filePath}`,
					method: 'PROPPATCH',
					auth: {
						username: user.userId,
						password: user.password,
					},
					headers: {
						'Content-Type': 'application/xml',
					},
					data: `<?xml version="1.0"?>
					<d:propertyupdate xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">
						<d:set>
							<d:prop>
								<oc:favorite>${favorite ? 1 : 0}</oc:favorite>
							</d:prop>
					  </d:set>
					</d:propertyupdate>`,
				})
				cy.log(`Created directory ${target}`, response)
			} catch (error) {
				cy.log('error', error)
				throw new Error('Unable to process fixture')
			}
		})
})

Cypress.Commands.add('mkdir', (user: User, target: string) => {
	// eslint-disable-next-line cypress/unsafe-to-chain-command
	return cy.clearCookies()
		.then(async () => {
			try {
				const rootPath = `${Cypress.env('baseUrl')}/remote.php/dav/files/${encodeURIComponent(user.userId)}`
				const filePath = target.split('/').map(encodeURIComponent).join('/')
				const response = await axios({
					url: `${rootPath}${filePath}`,
					method: 'MKCOL',
					auth: {
						username: user.userId,
						password: user.password,
					},
				})
				cy.log(`Created directory ${target}`, response)
				return response
			} catch (error) {
				cy.log('error', error)
				throw new Error('Unable to create directory')
			}
		})
})

Cypress.Commands.add('rm', (user: User, target: string) => {
	// eslint-disable-next-line cypress/unsafe-to-chain-command
	cy.clearCookies()
		.then(async () => {
			try {
				const rootPath = `${Cypress.env('baseUrl')}/remote.php/dav/files/${encodeURIComponent(user.userId)}`
				const filePath = target.split('/').map(encodeURIComponent).join('/')
				const response = await axios({
					url: `${rootPath}${filePath}`,
					method: 'DELETE',
					auth: {
						username: user.userId,
						password: user.password,
					},
				})
				cy.log(`delete file or directory ${target}`, response)
			} catch (error) {
				cy.log('error', error)
				throw new Error('Unable to delete file or directory')
			}
		})
})

/**
 * cy.uploadedContent - uploads a raw content
 * TODO: standardise in @nextcloud/cypress
 *
 * @param {User} user the owner of the file, e.g. admin
 * @param {Blob} blob the content to upload
 * @param {string} mimeType e.g. image/png
 * @param {string} target the target of the file relative to the user root
 */
Cypress.Commands.add('uploadContent', (user: User, blob: Blob, mimeType: string, target: string, mtime?: number) => {
	cy.clearCookies()
	return cy.then(async () => {
		const fileName = basename(target)

		// Process paths
		const rootPath = `${Cypress.env('baseUrl')}/remote.php/dav/files/${encodeURIComponent(user.userId)}`
		const filePath = target.split('/').map(encodeURIComponent).join('/')
		try {
			const file = new File([blob], fileName, { type: mimeType })
			const response = await axios({
				url: `${rootPath}${filePath}`,
				method: 'PUT',
				data: file,
				headers: {
					'Content-Type': mimeType,
					'X-OC-MTime': mtime ? `${mtime}` : undefined,
				},
				auth: {
					username: user.userId,
					password: user.password,
				},
			})
			cy.log(`Uploaded content as ${fileName}`, response)
			return response
		} catch (error) {
			cy.log('error', error)
			throw new Error('Unable to process fixture')
		}
	})
})

/**
 * Reset the admin theming entirely
 */
Cypress.Commands.add('resetAdminTheming', () => {
	const admin = new User('admin', 'admin')

	cy.clearCookies()
	cy.login(admin)

	// Clear all settings
	cy.request('/csrftoken').then(({ body }) => {
		const requestToken = body.token

		axios({
			method: 'POST',
			url: '/index.php/apps/theming/ajax/undoAllChanges',
			headers: {
				requesttoken: requestToken,
			},
		})
	})

	// Clear admin session
	cy.clearCookies()
})

/**
 * Reset the current or provided user theming settings
 * It does not reset the theme config as it is enforced in the
 * server config for cypress testing.
 */
Cypress.Commands.add('resetUserTheming', (user?: User) => {
	if (user) {
		cy.clearCookies()
		cy.login(user)
	}

	// Reset background config
	cy.request('/csrftoken').then(({ body }) => {
		const requestToken = body.token

		cy.request({
			method: 'POST',
			url: '/apps/theming/background/default',
			headers: {
				requesttoken: requestToken,
			},
		})
	})

	if (user) {
		// Clear current session
		cy.clearCookies()
	}
})
