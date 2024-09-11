<?php

declare(strict_types=1);
/**
 * SPDX-FileLicenseText: 2024 STRATO AG
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Theming\Themes\Ionos;

use OCA\Theming\ITheme;
use OCA\Theming\Themes\DefaultTheme;

class LightTheme extends DefaultTheme implements ITheme {

	public function getId(): string {
		return 'ionos-light';
	}

	public function getTitle(): string {
		return $this->l->t('IONOS light theme');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable IONOS light theme');
	}

	public function getDescription(): string {
		return $this->l->t('The IONOS light appearance.');
	}

	public function getMediaQuery(): string {
		return '(prefers-color-scheme: light)';
	}

	public function getMeta(): array {
		// https://html.spec.whatwg.org/multipage/semantics.html#meta-color-scheme
		return [[
			'name' => 'color-scheme',
			'content' => 'light',
		]];
	}

	public function getCSSVariables(): array {
		$defaultVariables = parent::getCSSVariables();

		return array_merge(
			$defaultVariables,
			[
			]
		);
	}
}
