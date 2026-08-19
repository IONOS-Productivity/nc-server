<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Theming\Tests\Themes;

use OCA\Theming\AppInfo\Application;
use OCA\Theming\ImageManager;
use OCA\Theming\ITheme;
use OCA\Theming\Service\BackgroundService;
use OCA\Theming\Themes\IonosTheme;
use OCA\Theming\ThemingDefaults;
use OCA\Theming\Util;
use OCP\App\IAppManager;
use OCP\Files\IAppData;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\ServerVersion;
use PHPUnit\Framework\MockObject\MockObject;

class IonosThemeTest extends AccessibleThemeTestCase {
	private ThemingDefaults&MockObject $themingDefaults;
	private IUserSession&MockObject $userSession;
	private IURLGenerator&MockObject $urlGenerator;
	private ImageManager&MockObject $imageManager;
	private IConfig&MockObject $config;
	private IL10N&MockObject $l10n;
	private IAppManager&MockObject $appManager;

	protected function setUp(): void {
		$this->themingDefaults = $this->createMock(ThemingDefaults::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->imageManager = $this->createMock(ImageManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->appManager = $this->createMock(IAppManager::class);

		$this->util = new Util(
			$this->createMock(ServerVersion::class),
			$this->config,
			$this->appManager,
			$this->createMock(IAppData::class),
			$this->imageManager
		);

		$this->themingDefaults->expects($this->any())
			->method('getColorPrimary')
			->willReturn('#003d8f');

		$this->themingDefaults->expects($this->any())
			->method('getDefaultColorPrimary')
			->willReturn('#003d8f');

		$this->themingDefaults->expects($this->any())
			->method('getColorBackground')
			->willReturn('#ffffff');

		$this->themingDefaults->expects($this->any())
			->method('getDefaultColorBackground')
			->willReturn('#ffffff');

		$this->themingDefaults->expects($this->any())
			->method('getBackground')
			->willReturn('/apps/' . Application::APP_ID . '/img/background/' . BackgroundService::DEFAULT_BACKGROUND_IMAGE);

		$this->l10n->expects($this->any())
			->method('t')
			->willReturnCallback(function ($text, $parameters = []) {
				return vsprintf($text, $parameters);
			});

		$this->urlGenerator->expects($this->any())
			->method('linkTo')
			->willReturnCallback(function (string $app, string $file) {
				return "/apps/$app/$file";
			});

		$this->urlGenerator->expects($this->any())
			->method('imagePath')
			->willReturnCallback(function ($app = 'core', $filename = '') {
				return "/$app/img/$filename";
			});

		$this->theme = new IonosTheme(
			$this->util,
			$this->themingDefaults,
			$this->userSession,
			$this->urlGenerator,
			$this->imageManager,
			$this->config,
			$this->l10n,
			$this->appManager,
			null,
		);

		parent::setUp();
	}

	public function testAccessibilityOfVariables(array $mainColors = [], array $backgroundColors = [], float $minContrast = 0): void {
		$this->markTestSkipped('IonosTheme uses CSS light-dark() functions — static hex contrast checker cannot evaluate them.');
	}

	public function testGetId(): void {
		$this->assertEquals('ionos', $this->theme->getId());
	}

	public function testGetType(): void {
		$this->assertEquals(ITheme::TYPE_THEME, $this->theme->getType());
	}

	public function testGetTitle(): void {
		$this->assertEquals('IONOS Theme', $this->theme->getTitle());
	}

	public function testGetEnableLabel(): void {
		$this->assertEquals('Enable the default IONOS Theme', $this->theme->getEnableLabel());
	}

	public function testGetDescription(): void {
		$this->assertEquals('The default IONOS HiDrive Next appearance.', $this->theme->getDescription());
	}

	public function testGetMediaQuery(): void {
		$this->assertEquals('all', $this->theme->getMediaQuery());
	}

	public function testGetMeta(): void {
		$meta = $this->theme->getMeta();
		$this->assertCount(1, $meta);
		$this->assertEquals('color-scheme', $meta[0]['name']);
		$this->assertEquals('light dark', $meta[0]['content']);
	}

	public function testGetCustomCssContainsFontFace(): void {
		$css = $this->theme->getCustomCss();
		$this->assertStringContainsString('@font-face', $css);
		$this->assertStringContainsString('Open sans', $css);
	}

	public function testGetCSSVariablesContainsIonosColors(): void {
		$variables = $this->theme->getCSSVariables();
		$this->assertArrayHasKey('--ion-color-blue-b7', $variables);
		$this->assertArrayHasKey('--color-primary-element', $variables);
		$this->assertArrayHasKey('--ion-color-cool-grey-c7', $variables);
	}
}
