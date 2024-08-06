/**
 * @copyright Copyright (c) 2022 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
/* eslint-disable no-console */
/* eslint-disable n/no-unpublished-import */
/* eslint-disable n/no-extraneous-import */

import Docker from 'dockerode'
import waitOn from 'wait-on'
import tar from 'tar'
import { execSync } from 'child_process'

export const docker = new Docker()

const CONTAINER_NAME = 'nextcloud-cypress-tests-server'
const SERVER_IMAGE = 'ghcr.io/nextcloud/continuous-integration-shallow-server'

/**
 * Start the testing container
 *
 * @param {string} branch the branch of your current work
 */
export const startNextcloud = async function(branch: string = getCurrentGitBranch()): Promise<any> {
	console.log('NO-OP: Starting Nextcloud container... 🚀')
	return "localhost:8080";
}

/**
 * Configure Nextcloud
 */
export const configureNextcloud = async function() {
	console.log('NO-OP: Configuring nextcloud...')
}

/**
 * Applying local changes to the container
 * Only triggered if we're not in CI. Otherwise the
 * continuous-integration-shallow-server image will
 * already fetch the proper branch.
 */
export const applyChangesToNextcloud = async function() {
	console.log('NO-OP: Apply local changes to nextcloud...')
}

/**
 * Force stop the testing container
 */
export const stopNextcloud = async function() {
	console.log('NO-OP: Stopping Nextcloud container...')
}

/**
 * Get the testing container's IP
 *
 * @param {Docker.Container} container the container to get the IP from
 */
export const getContainerIP = async function(
	container = docker.getContainer(CONTAINER_NAME),
): Promise<string> {
	let ip = ''
	let tries = 0
	while (ip === '' && tries < 10) {
		tries++

		await container.inspect(function(err, data) {
			if (err) {
				throw err
			}
			ip = data?.NetworkSettings?.IPAddress || ''
		})

		if (ip !== '') {
			break
		}

		await sleep(1000 * tries)
	}

	return ip
}

// Would be simpler to start the container from cypress.config.ts,
// but when checking out different branches, it can take a few seconds
// Until we can properly configure the baseUrl retry intervals,
// We need to make sure the server is already running before cypress
// https://github.com/cypress-io/cypress/issues/22676
export const waitOnNextcloud = async function(ip: string) {
	console.log('NO-OP: Waiting for Nextcloud to be ready... ⏳')
}

const runExec = async function(
	container: Docker.Container,
	command: string[],
	verbose = false,
	user = 'www-data',
) {
	const exec = await container.exec({
		Cmd: command,
		AttachStdout: true,
		AttachStderr: true,
		User: user,
	})

	return new Promise((resolve, reject) => {
		exec.start({}, (err, stream) => {
			if (err) {
				reject(err)
			}
			if (stream) {
				stream.setEncoding('utf-8')
				stream.on('data', str => {
					if (verbose && str.trim() !== '') {
						console.log(`├─ ${str.trim().replace(/\n/gi, '\n├─ ')}`)
					}
				})
				stream.on('end', resolve)
			}
		})
	})
}

const sleep = function(milliseconds: number) {
	return new Promise((resolve) => setTimeout(resolve, milliseconds))
}

const getCurrentGitBranch = function() {
	return execSync('git rev-parse --abbrev-ref HEAD').toString().trim() || 'master'
}
