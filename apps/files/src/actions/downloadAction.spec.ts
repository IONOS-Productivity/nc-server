/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { action } from './downloadAction'
import { expect } from '@jest/globals'
import {
	File,
	Folder,
	Permission,
	View,
	FileAction,
	DefaultType,
} from '@nextcloud/files'

const view = {
	id: 'files',
	name: 'Files',
} as View

// Mock webroot variable
beforeAll(() => {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	(window as any)._oc_webroot = ''
})

describe('Download action conditions tests', () => {
	test('Default values', () => {
		expect(action).toBeInstanceOf(FileAction)
		expect(action.id).toBe('download')
		expect(action.displayName([], view)).toBe('Download')
		expect(action.iconSvgInline([], view)).toBe('<svg>SvgMock</svg>')
		expect(action.default).toBe(DefaultType.DEFAULT)
		expect(action.order).toBe(30)
	})
})

describe('Download action enabled tests', () => {
	test('Enabled with READ permissions', () => {
		const file = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.ALL,
		})

		expect(action.enabled).toBeDefined()
		expect(action.enabled!([file], view)).toBe(true)
	})

	test('Disabled without READ permissions', () => {
		const file = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.NONE,
		})

		expect(action.enabled).toBeDefined()
		expect(action.enabled!([file], view)).toBe(false)
	})

	test('Disabled if not all nodes have READ permissions', () => {
		const folder1 = new Folder({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/Foo/',
			owner: 'admin',
			permissions: Permission.READ,
		})
		const folder2 = new Folder({
			id: 2,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/Bar/',
			owner: 'admin',
			permissions: Permission.NONE,
		})

		expect(action.enabled).toBeDefined()
		expect(action.enabled!([folder1], view)).toBe(true)
		expect(action.enabled!([folder2], view)).toBe(false)
		expect(action.enabled!([folder1, folder2], view)).toBe(false)
	})

	test('Disabled without nodes', () => {
		expect(action.enabled).toBeDefined()
		expect(action.enabled!([], view)).toBe(false)
	})
})

describe('Download action execute tests', () => {
	const link = {
		click: jest.fn(),
	} as unknown as HTMLAnchorElement

	beforeEach(() => {
		jest.spyOn(document, 'createElement').mockImplementation(() => link)
	})

	test('Download single file', async () => {
		const file = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.READ,
		})

		const exec = await action.exec(file, view, '/')

		// Silent action
		expect(exec).toBe(null)
		expect(link.download).toEqual('')
		expect(link.href).toEqual(
			'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
		)
		expect(link.click).toHaveBeenCalledTimes(1)
	})

	test('Download single file with batch', async () => {
		const file = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.READ,
		})

		const exec = await action.execBatch!([file], view, '/')

		// Silent action
		expect(exec).toStrictEqual([null])
		expect(link.download).toEqual('')
		expect(link.href).toEqual(
			'https://cloud.domain.com/remote.php/dav/files/admin/foobar.txt',
		)
		expect(link.click).toHaveBeenCalledTimes(1)
	})

	test('Download single folder', async () => {
		const folder = new Folder({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/FooBar/',
			owner: 'admin',
			permissions: Permission.READ,
		})

		const exec = await action.exec(folder, view, '/')

		// Silent action
		expect(exec).toBe(null)
		expect(link.download).toEqual('')
		expect(
			link.href.startsWith(
				'/index.php/apps/files/ajax/download.php?dir=%2F&files=%5B%22FooBar%22%5D&downloadStartSecret=',
			),
		).toBe(true)
		expect(link.click).toHaveBeenCalledTimes(1)
	})

	test('Download multiple nodes', async () => {
		const file1 = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/Dir/foo.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.READ,
		})
		const file2 = new File({
			id: 1,
			source: 'https://cloud.domain.com/remote.php/dav/files/admin/Dir/bar.txt',
			owner: 'admin',
			mime: 'text/plain',
			permissions: Permission.READ,
		})

		const exec = await action.execBatch!([file1, file2], view, '/Dir')

		// Silent action
		expect(exec).toStrictEqual([null, null])
		expect(link.download).toEqual('')
		expect(link.click).toHaveBeenCalledTimes(1)

		expect(link.href).toMatch(
			'/index.php/apps/files/ajax/download.php?dir=%2F&files=%5B%22foo.txt%22%2C%22bar.txt%22%5D&downloadStartSecret=',
		)
	})

	test('Download multiple nodes from different sources', async () => {
		const files = [
			new File({
				id: 1,
				source: 'https://cloud.domain.com/remote.php/dav/files/admin/Folder 1/foo.txt',
				owner: 'admin',
				mime: 'text/plain',
				permissions: Permission.READ,
			}),
			new File({
				id: 2,
				source: 'https://cloud.domain.com/remote.php/dav/files/admin/Folder 2/bar.txt',
				owner: 'admin',
				mime: 'text/plain',
				permissions: Permission.READ,
			}),
			new File({
				id: 3,
				source: 'https://cloud.domain.com/remote.php/dav/files/admin/Folder 2/baz.txt',
				owner: 'admin',
				mime: 'text/plain',
				permissions: Permission.READ,
			}),
		]

		const exec = await action.execBatch!(files, view, '/Dir')

		// Silent action
		expect(exec).toStrictEqual([null, null, null])
		expect(link.download).toEqual('')
		expect(link.click).toHaveBeenCalledTimes(1)

		expect(link.href).toMatch(
			'/index.php/apps/files/ajax/download.php?dir=%2F&files=%5B%22foo.txt%22%2C%22bar.txt%22%2C%22baz.txt%22%5D&downloadStartSecret=',
		)
	})

	test('Download node and parent folder', async () => {
		const files = [
			new File({
				id: 1,
				source: 'https://cloud.domain.com/remote.php/dav/files/admin/Folder 1/foo.txt',
				owner: 'admin',
				mime: 'text/plain',
				permissions: Permission.READ,
			}),
			new Folder({
				id: 2,
				source: 'https://cloud.domain.com/remote.php/dav/files/admin/Folder 1',
				owner: 'admin',
				permissions: Permission.READ,
			}),
		]

		const exec = await action.execBatch!(files, view, '/Dir')

		// Silent action
		expect(exec).toStrictEqual([null, null])
		expect(link.download).toEqual('')
		expect(link.click).toHaveBeenCalledTimes(1)

		expect(link.href).toMatch(
			'/index.php/apps/files/ajax/download.php?dir=%2F&files=%5B%22foo.txt%22%2C%22Folder%201%22%5D&downloadStartSecret=',
		)
	})
})
