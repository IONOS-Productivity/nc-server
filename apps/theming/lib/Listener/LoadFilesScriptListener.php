<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Theming\Listener;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

/** @template-implements IEventListener<LoadAdditionalScriptsEvent|BeforeTemplateRenderedEvent> */
class LoadFilesScriptListener implements IEventListener {

	public function handle(Event $event): void {
		if ($event instanceof BeforeTemplateRenderedEvent) {
			// Public share pages render the file list without LoadAdditionalScriptsEvent;
			// skip the password/auth page, which has no file list
			if ($event->getScope() === BeforeTemplateRenderedEvent::SCOPE_PUBLIC_SHARE_AUTH) {
				return;
			}
		} elseif (!($event instanceof LoadAdditionalScriptsEvent)) {
			return;
		}

		// IONOS-specific runtime fixes and enhancements, scoped to the Files app only
		Util::addScript('theming', 'ionos');
	}
}
