<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 STRATO AG
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Theming\Listener;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

/**
 * @template-implements IEventListener<LoadAdditionalScriptsEvent>
 */
class LoadFilesScriptListener implements IEventListener {

	public function handle(Event $event): void {
		if (!($event instanceof LoadAdditionalScriptsEvent)) {
			return;
		}

		// IONOS-only translations; init scripts run before the Files app main script renders
		Util::addInitScript('theming', 'ionos-l10n');
	}
}
