<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Settings\SetupChecks;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\SetupCheck\ISetupCheck;
use OCP\SetupCheck\SetupResult;
use OCP\TaskProcessing\IManager;

class TaskProcessingPickupSpeed implements ISetupCheck {
	public const MAX_SLOW_PERCENTAGE = 0.2;
<<<<<<< HEAD

	public const MAX_DAYS = 14;
=======
	public const TIME_SPAN = 24;
>>>>>>> ionos-dev

	public function __construct(
		private IL10N $l10n,
		private IManager $taskProcessingManager,
		private ITimeFactory $timeFactory,
	) {
	}

	public function getCategory(): string {
		return 'ai';
	}

	public function getName(): string {
		return $this->l10n->t('Task Processing pickup speed');
	}

	public function run(): SetupResult {
<<<<<<< HEAD
		$taskCount = 0;
		$lastNDays = 1;
		while ($taskCount === 0 && $lastNDays < self::MAX_DAYS) {
			$lastNDays++;
			// userId: '' means no filter, whereas null would mean guest
			$tasks = $this->taskProcessingManager->getTasks(userId: '', scheduleAfter: $this->timeFactory->now()->getTimestamp() - (60 * 60 * 24 * $lastNDays));
			$taskCount = count($tasks);
		}
		if ($taskCount === 0) {
			return SetupResult::success(
				$this->l10n->n(
					'No scheduled tasks in the last day.',
					'No scheduled tasks in the last %n days.',
					$lastNDays
				)
			);
=======
		$tasks = $this->taskProcessingManager->getTasks(userId: '', scheduleAfter: $this->timeFactory->now()->getTimestamp() - 60 * 60 * self::TIME_SPAN); // userId: '' means no filter, whereas null would mean guest
		$taskCount = count($tasks);
		if ($taskCount === 0) {
			return SetupResult::success($this->l10n->n('No scheduled tasks in the last %n hour.', 'No scheduled tasks in the last %n hours.', self::TIME_SPAN));
>>>>>>> ionos-dev
		}
		$slowCount = 0;
		foreach ($tasks as $task) {
			if ($task->getStartedAt() === null) {
				continue; // task was not picked up yet
			}
			if ($task->getScheduledAt() === null) {
				continue; // task was not scheduled yet -- should not happen, but the API specifies null as return value
			}
			$pickupDelay = $task->getScheduledAt() - $task->getStartedAt();
			if ($pickupDelay > 60 * 4) {
				$slowCount++; // task pickup took longer than 4 minutes
			}
		}

<<<<<<< HEAD
		if (($slowCount / $taskCount) < self::MAX_SLOW_PERCENTAGE) {
			return SetupResult::success(
				$this->l10n->n(
					'The task pickup speed has been ok in the last day.',
					'The task pickup speed has been ok in the last %n days.',
					$lastNDays
				)
			);
		} else {
			return SetupResult::warning(
				$this->l10n->n(
					'The task pickup speed has been slow in the last day. Many tasks took longer than 4 minutes to be picked up. Consider setting up a worker to process tasks in the background.',
					'The task pickup speed has been slow in the last %n days. Many tasks took longer than 4 minutes to be picked up. Consider setting up a worker to process tasks in the background.',
					$lastNDays
				),
				'https://docs.nextcloud.com/server/latest/admin_manual/ai/overview.html#improve-ai-task-pickup-speed'
			);
=======
		if ($slowCount / $taskCount < self::MAX_SLOW_PERCENTAGE) {
			return SetupResult::success($this->l10n->n('The task pickup speed has been ok in the last %n hour.', 'The task pickup speed has been ok in the last %n hours.', self::TIME_SPAN));
		} else {
			return SetupResult::warning($this->l10n->n('The task pickup speed has been slow in the last %n hour. Many tasks took longer than 4 minutes to be picked up. Consider setting up a worker to process tasks in the background.', 'The task pickup speed has been slow in the last %n hours. Many tasks took longer than 4 minutes to be picked up. Consider setting up a worker to process tasks in the background.', self::TIME_SPAN), 'https://docs.nextcloud.com/server/latest/admin_manual/ai/overview.html#improve-ai-task-pickup-speed');
>>>>>>> ionos-dev
		}
	}
}
