/**
 * SPDX-FileCopyrightText: 2026 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// Keep files-list__header-grid-button aligned with the sticky thead.
// Everything above the table (files-list__before with workspace/folder-description headers,
// files-list__filters with the active filter chips) has variable height, so we measure where
// the table starts relative to the list, and expose a CSS variable for the button's top position.
;(function() {
	'use strict'

	function setupFilesListTracking() {
		const fl = document.querySelector('#app-content-vue .files-list')
		const ac = document.getElementById('app-content-vue')
		if (!fl || !ac) return false

		let rafHandle = null

		function update() {
			rafHandle = null
			const table = fl.querySelector('.files-list__table')
			const offset = table ? Math.max(0, table.getBoundingClientRect().top - fl.getBoundingClientRect().top) : 0
			ac.style.setProperty('--files-list-grid-button-top', (fl.offsetTop + offset) + 'px')
		}

		function scheduleUpdate() {
			rafHandle = rafHandle || requestAnimationFrame(update)
		}

		// Elements above the table come and go (e.g. the filter chips), so observe every
		// non-table child of the list and re-observe whenever the children change.
		const resizeObserver = new ResizeObserver(scheduleUpdate)
		function observeChildren() {
			resizeObserver.disconnect()
			resizeObserver.observe(fl)
			for (const child of fl.children) {
				if (!child.classList.contains('files-list__table')) resizeObserver.observe(child)
			}
			scheduleUpdate()
		}

		new MutationObserver(observeChildren).observe(fl, { childList: true })
		fl.addEventListener('scroll', scheduleUpdate, { passive: true })
		observeChildren()
		update()

		return true
	}

	document.addEventListener('DOMContentLoaded', function() {
		const ac = document.getElementById('app-content-vue')
		if (!ac) return

		if (!setupFilesListTracking()) {
			const mo = new MutationObserver(function() {
				if (setupFilesListTracking()) mo.disconnect()
			})
			mo.observe(ac, { childList: true, subtree: true })
		}
	})
})()
