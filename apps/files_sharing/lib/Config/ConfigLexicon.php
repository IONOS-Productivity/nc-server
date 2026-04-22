<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Sharing\Config;

<<<<<<< HEAD
use OCP\Config\Lexicon\Entry;
use OCP\Config\Lexicon\ILexicon;
use OCP\Config\Lexicon\Strictness;
use OCP\Config\ValueType;
=======
use NCU\Config\Lexicon\ConfigLexiconEntry;
use NCU\Config\Lexicon\ConfigLexiconStrictness;
use NCU\Config\Lexicon\IConfigLexicon;
use NCU\Config\ValueType;
>>>>>>> ionos-dev

/**
 * Config Lexicon for files_sharing.
 *
 * Please Add & Manage your Config Keys in that file and keep the Lexicon up to date!
 *
<<<<<<< HEAD
 * {@see ILexicon}
 */
class ConfigLexicon implements ILexicon {
	public const SHOW_FEDERATED_AS_INTERNAL = 'show_federated_shares_as_internal';
	public const SHOW_FEDERATED_TO_TRUSTED_AS_INTERNAL = 'show_federated_shares_to_trusted_servers_as_internal';

	public function getStrictness(): Strictness {
		return Strictness::IGNORE;
=======
 * {@see IConfigLexicon}
 */
class ConfigLexicon implements IConfigLexicon {
	public const SHOW_FEDERATED_AS_INTERNAL = 'show_federated_shares_as_internal';

	public function getStrictness(): ConfigLexiconStrictness {
		return ConfigLexiconStrictness::IGNORE;
>>>>>>> ionos-dev
	}

	public function getAppConfigs(): array {
		return [
<<<<<<< HEAD
			new Entry(self::SHOW_FEDERATED_AS_INTERNAL, ValueType::BOOL, false, 'shows federated shares as internal shares', true),
			new Entry(self::SHOW_FEDERATED_TO_TRUSTED_AS_INTERNAL, ValueType::BOOL, false, 'shows federated shares to trusted servers as internal shares', true),
=======
			new ConfigLexiconEntry(self::SHOW_FEDERATED_AS_INTERNAL, ValueType::BOOL, false, 'shows federated shares as internal shares', true),
>>>>>>> ionos-dev
		];
	}

	public function getUserConfigs(): array {
		return [];
	}
}
