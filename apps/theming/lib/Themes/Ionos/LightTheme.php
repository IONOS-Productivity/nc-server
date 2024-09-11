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
		$originalFontFace = $defaultVariables['--font-face'];

		return array_merge(
			$defaultVariables,
			[
			'--font-face' => '"Open sans", ' . $originalFontFace
			]
		);
	}

	public function getCustomCss(): string {
		$regularEot = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Regular-webfont.eot');
		$regularWoff = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Regular-webfont.woff');
		$regularWoff2 = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Regular-webfont.woff2');
		$regularTtf = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Regular-webfont.ttf');
		$regularSvg = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Regular-webfont.svg#open_sansregular');

		$semiBoldEot = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-SemiBold-webfont.eot');
		$semiBoldWoff = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-SemiBold-webfont.woff');
		$semiBoldWoff2 = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-SemiBold-webfont.woff2');
		$semiBoldTtf = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-SemiBold-webfont.ttf');
		$semiBoldSvg = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-SemiBold-webfont.svg#open_sansregular');

		$boldEot = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Bold-webfont.eot');
		$boldWoff = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Bold-webfont.woff');
		$boldWoff2 = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Bold-webfont.woff2');
		$boldTtf = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Bold-webfont.ttf');
		$boldSvg = $this->urlGenerator->linkTo('theming', 'fonts/OpenSans/OpenSans-Bold-webfont.svg#open_sansregular');

		return "
		@font-face {
			font-family: 'Open sans';
			src: url('$regularEot') format('embedded-opentype'),
				url('$regularWoff') format('woff'),
				url('$regularWoff2') format('woff2'),
				url('$regularTtf') format('truetype'),
				url('$regularSvg') format('svg');
			font-weight: normal;
			font-style: normal;
			font-display: swap;
		}

		/* Open sans semi-bold variant */
		@font-face {
			font-family: 'Open sans';
			src: url('$semiBoldEot') format('embedded-opentype'),
				url('$semiBoldWoff') format('woff'),
				url($semiBoldWoff2) format('woff2'),
				url('$semiBoldTtf') format('truetype'),
				url('$semiBoldSvg') format('svg');
			font-weight: 600;
			font-style: normal;
			font-display: swap;
		}

		/* Open sans bold variant */
		@font-face {
			font-family: 'Open sans';
			src: url('$boldEot') format('embedded-opentype'),
				url('$boldWoff') format('woff'),
				url('$boldWoff2') format('woff2'),
				url('$boldTtf') format('truetype'),
				url('$boldSvg') format('svg');
			font-weight: bold;
			font-style: normal;
			font-display: swap;
		}
		";
	}
}
