<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2025 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Theming\Themes;

use OCA\Theming\ITheme;

class IonosTheme extends DefaultTheme implements ITheme {

	private const THEME_ID = 'ionos';
	private const FONT_FAMILY = 'Open sans';
	private const FONT_PATH_PREFIX = 'fonts/OpenSans/';

	private static ?array $cachedCssFiles = null;

	public function getId(): string {
		return self::THEME_ID;
	}

	public function getTitle(): string {
		return $this->l->t('IONOS Theme');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable the default IONOS Theme');
	}

	public function getDescription(): string {
		return $this->l->t('The default IONOS HiDrive Next appearance.');
	}

	public function getMediaQuery(): string {
		return 'all';
	}

	public function getMeta(): array {
		return [[
			'name' => 'color-scheme',
			'content' => 'light dark',
		]];
	}

	public function getCustomCss(): string {
		return $this->loadCustomCssFiles() . PHP_EOL . $this->generateFontFacesCss();
	}

	private function getCssFiles(): array {
		if (self::$cachedCssFiles !== null) {
			return self::$cachedCssFiles;
		}

		$cssDir = __DIR__ . '/../../css/' . self::THEME_ID . '/';
		$files = glob($cssDir . '*.css');
		if ($files === false) {
			self::$cachedCssFiles = [];
			return [];
		}

		$cssFiles = [];
		foreach ($files as $file) {
			$cssFiles[] = basename($file);
		}
		sort($cssFiles);
		self::$cachedCssFiles = $cssFiles;

		return $cssFiles;
	}

	private function loadCustomCssFiles(): string {
		$customCss = '';
		foreach ($this->getCssFiles() as $file) {
			$filePath = __DIR__ . '/../../css/' . self::THEME_ID . '/' . $file;
			if (!is_readable($filePath)) {
				continue;
			}
			$content = file_get_contents($filePath);
			if ($content !== false) {
				$customCss .= $content . PHP_EOL;
			}
		}
		return rtrim($customCss, PHP_EOL);
	}

	private function generateFontFacesCss(): string {
		$variants = [
			['file' => 'Regular', 'weight' => 'normal', 'comment' => ''],
			['file' => 'SemiBold', 'weight' => '600', 'comment' => '/* Open sans semi-bold variant */'],
			['file' => 'Bold', 'weight' => 'bold', 'comment' => '/* Open sans bold variant */'],
		];

		$css = '';
		foreach ($variants as $variant) {
			$css .= $this->generateSingleFontFace($variant['file'], $variant['weight'], $variant['comment']);
		}
		return $css;
	}

	private function generateSingleFontFace(string $fileVariant, string $weight, string $comment): string {
		$base = self::FONT_PATH_PREFIX . 'OpenSans-' . $fileVariant . '-webfont';
		$eot   = $this->urlGenerator->linkTo('theming', $base . '.eot');
		$woff  = $this->urlGenerator->linkTo('theming', $base . '.woff');
		$woff2 = $this->urlGenerator->linkTo('theming', $base . '.woff2');
		$ttf   = $this->urlGenerator->linkTo('theming', $base . '.ttf');
		$svg   = $this->urlGenerator->linkTo('theming', $base . '.svg#OpenSans');

		return "
		$comment
		@font-face {
			font-family: '" . self::FONT_FAMILY . "';
			src: url('$eot') format('embedded-opentype'),
				url('$woff') format('woff'),
				url('$woff2') format('woff2'),
				url('$ttf') format('truetype'),
				url('$svg') format('svg');
			font-weight: $weight;
			font-style: normal;
			font-display: swap;
		}
		";
	}

	public function getCSSVariables(): array {
		$defaultVariables = parent::getCSSVariables();
		$originalFontFace = $defaultVariables['--font-face'];

		// IONOS COLORS
		$ionColorMainBackground = 'light-dark(#fff, var(--ion-color-blue-b9))';
		$ionColorPrimary = '#003d8f';
		$ionColorBlueB1 = '#dbedf8';
		$ionColorBlueB2 = '#95caeb';
		$ionColorBlueB3 = '#3196D6';
		$ionColorBlueB4 = '#1474c4';
		$ionColorBlueB5 = '#095BB1';
		$ionColorBlueB6 = '#003D8F';
		$ionColorBlueB7 = '#0B2A63';
		$ionColorBlueB8 = '#001B41';
		$ionColorBlueB9 = '#02102B';
		$ionColorCoolGreyC1 = '#f4f7fa';
		$ionColorCoolGreyC2 = '#dbe2e8';
		$ionColorCoolGreyC3 = '#bcc8d4';
		$ionColorCoolGreyC4 = '#97A3B4';
		$ionColorCoolGreyC5 = '#718095';
		$ionColorCoolGreyC6 = '#465A75';
		$ionColorCoolGreyC7 = '#2E4360';
		$ionColorCoolGreyC8 = '#1D2D42';
		$ionColorTypoMild = 'light-dark(var(--ion-color-cool-grey-c7), var(--ion-color-cool-grey-c1))';
		$ionColorLightGrey = '#d7d7d7';
		$ionColorGreenG3 = '#12cf76';
		$ionColorRoseR3 = '#ff6159';
		$ionColorSkyS3 = '#11c7e6';
		$ionColorAmberY3 = '#ffaa00';
		$ionColorAmberY4 = '#EF8300';
		$ionColorAmberY5 = '#c36b00';
		$ionColorAmberY6 = '#8E4E00';

		$ionosVariables = [
			'--ion-color-main-background' => $ionColorMainBackground,
			'--ion-color-primary' => $ionColorPrimary,
			'--ion-color-secondary' => $ionColorBlueB8,
			'--ion-color-blue-b1' => $ionColorBlueB1,
			'--ion-color-blue-b2' => $ionColorBlueB2,
			'--ion-color-blue-b3' => $ionColorBlueB3,
			'--ion-color-blue-b4' => $ionColorBlueB4,
			'--ion-color-blue-b5' => $ionColorBlueB5,
			'--ion-color-blue-b6' => $ionColorBlueB6,
			'--ion-color-blue-b7' => $ionColorBlueB7,
			'--ion-color-blue-b8' => $ionColorBlueB8,
			'--ion-color-blue-b9' => $ionColorBlueB9,
			'--ion-color-cool-grey-c1' => $ionColorCoolGreyC1,
			'--ion-color-cool-grey-c2' => $ionColorCoolGreyC2,
			'--ion-color-cool-grey-c3' => $ionColorCoolGreyC3,
			'--ion-color-cool-grey-c4' => $ionColorCoolGreyC4,
			'--ion-color-cool-grey-c5' => $ionColorCoolGreyC5,
			'--ion-color-cool-grey-c6' => $ionColorCoolGreyC6,
			'--ion-color-cool-grey-c7' => $ionColorCoolGreyC7,
			'--ion-color-cool-grey-c8' => $ionColorCoolGreyC8,
			'--ion-color-typo-mild' => $ionColorTypoMild,
			'--ion-color-light-grey' => $ionColorLightGrey,
			'--ion-color-green-g3' => $ionColorGreenG3,
			'--ion-color-rose-r3' => $ionColorRoseR3,
			'--ion-color-sky-s3' => $ionColorSkyS3,
			'--ion-color-amber-y3' => $ionColorAmberY3,
			'--ion-color-amber-y4' => $ionColorAmberY4,
			'--ion-color-amber-y5' => $ionColorAmberY5,
			'--ion-color-amber-y6' => $ionColorAmberY6,
		];

		$colorMainBackground = '#fff';
		$colorMainBackgroundRGB = join(',', $this->util->hexToRGB($colorMainBackground));
		$colorBoxShadow = $this->util->darken($colorMainBackground, 70);
		$colorBoxShadowRGB = join(',', $this->util->hexToRGB($colorBoxShadow));
		$colorPrimary = $ionColorPrimary;
		$colorShadowHeader = 'light-dark(rgba(113, 128, 149, 0.5), rgba(113, 128, 149, 0.2))';

		$colorError = $ionColorRoseR3;
		$colorWarning = $ionColorAmberY3;
		$colorSuccess = $ionColorGreenG3;
		$colorInfo = $ionColorSkyS3;

		$variables = [
			'--color-main-background' => $ionColorMainBackground,
			'--color-main-background-rgb' => $colorMainBackgroundRGB,
			'--color-main-background-translucent' => 'light-dark(rgba(var(--color-main-background-rgb), .97), var(--ion-color-cool-grey-c8))',
			'--color-main-background-blur' => 'light-dark(rgba(var(--color-main-background-rgb), .8), rgba(29, 45, 66, .95))',
			'--color-primary' => $colorPrimary,
			'--color-primary-element' => 'light-dark(var(--ion-color-blue-b6), var(--ion-color-blue-b4))',
			'--color-primary-text' => 'var(--color-text-maxcontrast)',
			'--gradient-main-background' => 'var(--color-main-background) 0%, var(--color-main-background-translucent) 85%, transparent 100%',
			'--color-background-hover' => 'light-dark(var(--ion-color-blue-b1), var(--ion-color-cool-grey-c7))',
			'--color-background-dark' => $this->util->darken($colorMainBackground, 7),
			'--color-background-darker' => $this->util->darken($colorMainBackground, 14),
			'--color-placeholder-light' => $this->util->darken($colorMainBackground, 10),
			'--color-placeholder-dark' => $this->util->darken($colorMainBackground, 20),
			'--color-main-text' => $ionColorTypoMild,
			'--color-text-maxcontrast' => $ionColorTypoMild,
			'--color-text-maxcontrast-default' => $ionColorTypoMild,
			'--color-text-maxcontrast-background-blur' => $ionColorTypoMild,
			'--color-text-light' => 'var(--color-main-text)',
			'--color-text-lighter' => 'var(--color-text-maxcontrast)',
			'--color-scrollbar' => $ionColorTypoMild,
			'--color-error' => $colorError,
			'--color-error-rgb' => join(',', $this->util->hexToRGB($colorError)),
			'--color-error-hover' => $this->util->mix($colorError, $colorMainBackground, 75),
			'--color-error-text' => $this->util->darken($colorError, 5),
			'--color-warning' => $colorWarning,
			'--color-warning-rgb' => join(',', $this->util->hexToRGB($colorWarning)),
			'--color-warning-hover' => $this->util->darken($colorWarning, 5),
			'--color-warning-text' => $this->util->darken($colorWarning, 7),
			'--color-success' => $colorSuccess,
			'--color-success-rgb' => join(',', $this->util->hexToRGB($colorSuccess)),
			'--color-success-hover' => $this->util->mix($colorSuccess, $colorMainBackground, 80),
			'--color-success-text' => $this->util->darken($colorSuccess, 4),
			'--color-info' => $colorInfo,
			'--color-info-rgb' => join(',', $this->util->hexToRGB($colorInfo)),
			'--color-info-hover' => $this->util->mix($colorInfo, $colorMainBackground, 80),
			'--color-info-text' => $this->util->darken($colorInfo, 4),
			'--color-favorite' => $ionColorAmberY3,
			'--color-loading-light' => '#cccccc',
			'--color-loading-dark' => '#444444',
			'--color-box-shadow-rgb' => $colorBoxShadowRGB,
			'--color-box-shadow' => 'rgba(var(--color-box-shadow-rgb), 0.5)',
			'--color-border' => 'light-dark(' . $this->util->darken($colorMainBackground, 7) . ', rgba(255, 255, 255, 0.1))',
			'--color-border-dark' => $this->util->darken($colorMainBackground, 14),
			'--color-border-maxcontrast' => $this->util->darken($colorMainBackground, 51),
			'--color-shadow-header' => $colorShadowHeader,
			'--default-font-size' => '15px',
			'--animation-quick' => '100ms',
			'--animation-slow' => '300ms',
			'--border-width-input' => '1px',
			'--border-width-input-focused' => '2px',
			'--border-radius' => '3px',
			'--border-radius-large' => '10px',
			'--border-radius-rounded' => '28px',
			'--border-radius-pill' => '100px',
			'--default-clickable-area' => '44px',
			'--default-line-height' => '24px',
			'--default-grid-baseline' => '4px',
			'--header-height' => '50px',
			'--navigation-width' => '300px',
			'--sidebar-min-width' => '300px',
			'--sidebar-max-width' => '500px',
			'--list-min-width' => '200px',
			'--list-max-width' => '300px',
			'--header-menu-item-height' => '44px',
			'--header-menu-profile-item-height' => '66px',
			'--breakpoint-mobile' => '1024px',
			'--background-invert-if-dark' => 'no',
			'--background-invert-if-bright' => 'invert(100%)',
			'--background-image-invert-if-bright' => 'no',
			'--background-image-color-text' => '#ffffff',
		];

		return array_merge(
			$defaultVariables,
			$ionosVariables,
			$variables,
			['--font-face' => '"' . self::FONT_FAMILY . '", ' . $originalFontFace]
		);
	}
}
