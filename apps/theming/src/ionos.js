/**
 * SPDX-FileCopyrightText: 2026 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// Keep files-list__header-grid-button aligned with the sticky thead.
// The files-list__before area (workspace/folder-description headers) has variable height,
// so we track its height + scroll offset and expose a CSS variable for the button's top position.
;(function() {
	'use strict'

	function setupFilesListTracking() {
		const fl = document.querySelector('#app-content-vue .files-list')
		const ac = document.getElementById('app-content-vue')
		if (!fl || !ac) return false

		const before = fl.querySelector('.files-list__before')
		let rafHandle = null

		function update() {
			rafHandle = null
			const top = fl.offsetTop + Math.max(0, (before ? before.offsetHeight : 0) - fl.scrollTop)
			ac.style.setProperty('--files-list-grid-button-top', top + 'px')
		}

		function scheduleUpdate() {
			rafHandle = rafHandle || requestAnimationFrame(update)
		}

		new ResizeObserver(scheduleUpdate).observe(fl)
		if (before) new ResizeObserver(scheduleUpdate).observe(before)
		fl.addEventListener('scroll', scheduleUpdate, { passive: true })
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
