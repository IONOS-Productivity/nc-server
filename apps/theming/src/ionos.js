/**
 * SPDX-FileCopyrightText: 2026 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// Keep files-list__header-grid-button aligned with the sticky thead.
// Everything above the table (files-list__before with workspace/folder-description headers,
// files-list__filters with the active filter chips) has variable height, so we measure where
// the table starts relative to the list, and expose a CSS variable for the button's top position.
// The button is centered vertically on the header row, whose height differs between the Files app
// and public shares.
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
			// An empty list renders the empty view above a hidden (but space-taking) table.
			// Anchor on the empty view then, otherwise the toggle ends up below it.
			const anchor = table && !table.classList.contains('files-list__table--hidden')
				? table
				: fl.querySelector('.files-list__empty') || table
			const offset = anchor ? Math.max(0, anchor.getBoundingClientRect().top - fl.getBoundingClientRect().top) : 0
			const thead = fl.querySelector('.files-list__thead')
			const button = ac.querySelector('.files-list__header-grid-button')
			const centering = thead && button ? Math.max(0, (thead.offsetHeight - button.offsetHeight) / 2) : 0
			ac.style.setProperty('--files-list-grid-button-top', (fl.offsetTop + offset + centering) + 'px')
		}

		function scheduleUpdate() {
			rafHandle = rafHandle || requestAnimationFrame(update)
		}

		// Elements above the table come and go (e.g. the filter chips, the empty view), so
		// observe every non-table child of the list and re-observe whenever the children change.
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
