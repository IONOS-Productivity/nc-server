<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Sharing\Listener;

use OC\InitialStateService;
use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files_Sharing\AppInfo\Application;
use OCA\Files_Sharing\External\Manager as ExternalManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUserSession;
use OCP\Server;
use OCP\Share\IManager;
use OCP\Share\IShare;
use OCP\Util;

/** @template-implements IEventListener<LoadAdditionalScriptsEvent> */
class LoadAdditionalListener implements IEventListener {
	#[\Override]
	public function handle(Event $event): void {
		if (!($event instanceof LoadAdditionalScriptsEvent)) {
			return;
		}

		// After files for the breadcrumb share indicator
		Util::addScript(Application::APP_ID, 'additionalScripts', 'files');
		Util::addStyle(Application::APP_ID, 'icons');

		$shareManager = Server::get(IManager::class);
		if ($shareManager->shareApiEnabled()) {
			Util::addInitScript(Application::APP_ID, 'init');
		}

		$this->provideInitialStates();
	}

	private function provideInitialStates(): void {
		$userSession = Server::get(IUserSession::class);
		$user = $userSession->getUser();
		if ($user === null) {
			return;
		}

		$initialState = Server::get(InitialStateService::class);
		$shareManager = Server::get(IManager::class);
		$externalManager = Server::get(ExternalManager::class);

		$hasPendingShares = $this->hasPendingShares($shareManager, $externalManager, $user->getUID());
		$initialState->provideInitialState(Application::APP_ID, 'has_pending_shares', $hasPendingShares);
	}

	private function hasPendingShares(IManager $shareManager, ExternalManager $externalManager, string $userId): bool {
		foreach ([IShare::TYPE_USER, IShare::TYPE_GROUP] as $shareType) {
			$shares = $shareManager->getSharedWith($userId, $shareType, null, -1, 0);
			foreach ($shares as $share) {
				if ($share->getStatus() === IShare::STATUS_PENDING || $share->getStatus() === IShare::STATUS_REJECTED) {
					return true;
				}
			}
		}

		return !empty($externalManager->getOpenShares());
	}
}
