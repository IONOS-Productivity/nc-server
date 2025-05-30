<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Test\App;

use OC\App\AppManager;
use OC\AppConfig;
use OCP\App\AppPathNotFoundException;
use OCP\App\Events\AppDisableEvent;
use OCP\App\Events\AppEnableEvent;
use OCP\App\IAppManager;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class AppManagerTest
 *
 * @package Test\App
 */
class AppManagerTest extends TestCase {
	/**
	 * @return AppConfig|MockObject
	 */
	protected function getAppConfig() {
		$appConfig = [];
		$config = $this->createMock(AppConfig::class);

		$config->expects($this->any())
			->method('getValue')
			->willReturnCallback(function ($app, $key, $default) use (&$appConfig) {
				return (isset($appConfig[$app]) and isset($appConfig[$app][$key])) ? $appConfig[$app][$key] : $default;
			});
		$config->expects($this->any())
			->method('setValue')
			->willReturnCallback(function ($app, $key, $value) use (&$appConfig) {
				if (!isset($appConfig[$app])) {
					$appConfig[$app] = [];
				}
				$appConfig[$app][$key] = $value;
			});
		$config->expects($this->any())
			->method('getValues')
			->willReturnCallback(function ($app, $key) use (&$appConfig) {
				if ($app) {
					return $appConfig[$app];
				} else {
					$values = [];
					foreach ($appConfig as $appid => $appData) {
						if (isset($appData[$key])) {
							$values[$appid] = $appData[$key];
						}
					}
					return $values;
				}
			});

		return $config;
	}

	/** @var IUserSession|MockObject */
	protected $userSession;

	/** @var IConfig|MockObject */
	private $config;

	/** @var IGroupManager|MockObject */
	protected $groupManager;

	/** @var AppConfig|MockObject */
	protected $appConfig;

	/** @var ICache|MockObject */
	protected $cache;

	/** @var ICacheFactory|MockObject */
	protected $cacheFactory;

	/** @var IEventDispatcher|MockObject */
	protected $eventDispatcher;

	/** @var LoggerInterface|MockObject */
	protected $logger;

	protected IURLGenerator&MockObject $urlGenerator;

	/** @var IAppManager */
	protected $manager;

	protected function setUp(): void {
		parent::setUp();

		$this->userSession = $this->createMock(IUserSession::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->appConfig = $this->getAppConfig();
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cache = $this->createMock(ICache::class);
		$this->eventDispatcher = $this->createMock(IEventDispatcher::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->overwriteService(AppConfig::class, $this->appConfig);
		$this->overwriteService(IURLGenerator::class, $this->urlGenerator);

		$this->cacheFactory->expects($this->any())
			->method('createDistributed')
			->with('settings')
			->willReturn($this->cache);

		$this->config
			->method('getSystemValueBool')
			->with('installed', false)
			->willReturn(true);

		$this->manager = new AppManager(
			$this->userSession,
			$this->config,
			$this->groupManager,
			$this->cacheFactory,
			$this->eventDispatcher,
			$this->logger,
		);
	}

	protected function tearDown(): void {
		parent::tearDown();

		// Reset static OC_Util
		$util = new \OC_Util();
		self::invokePrivate($util, 'versionCache', [null]);
	}

	/**
	 * @dataProvider dataGetAppIcon
	 */
	public function testGetAppIcon($callback, ?bool $dark, string|null $expected) {
		$this->urlGenerator->expects($this->atLeastOnce())
			->method('imagePath')
			->willReturnCallback($callback);

		if ($dark !== null) {
			$this->assertEquals($expected, $this->manager->getAppIcon('test', $dark));
		} else {
			$this->assertEquals($expected, $this->manager->getAppIcon('test'));
		}
	}

	public function dataGetAppIcon(): array {
		$nothing = function ($appId) {
			$this->assertEquals('test', $appId);
			throw new \RuntimeException();
		};

		$createCallback = function ($workingIcons) {
			return function ($appId, $icon) use ($workingIcons) {
				$this->assertEquals('test', $appId);
				if (in_array($icon, $workingIcons)) {
					return '/path/' . $icon;
				}
				throw new \RuntimeException();
			};
		};

		return [
			'does not find anything' => [
				$nothing,
				false,
				null,
			],
			'nothing if request dark but only bright available' => [
				$createCallback(['app.svg']),
				true,
				null,
			],
			'nothing if request bright but only dark available' => [
				$createCallback(['app-dark.svg']),
				false,
				null,
			],
			'bright and only app.svg' => [
				$createCallback(['app.svg']),
				false,
				'/path/app.svg',
			],
			'dark and only app-dark.svg' => [
				$createCallback(['app-dark.svg']),
				true,
				'/path/app-dark.svg',
			],
			'dark only appname -dark.svg' => [
				$createCallback(['test-dark.svg']),
				true,
				'/path/test-dark.svg',
			],
			'bright and only appname.svg' => [
				$createCallback(['test.svg']),
				false,
				'/path/test.svg',
			],
			'priotize custom over default' => [
				$createCallback(['app.svg', 'test.svg']),
				false,
				'/path/test.svg',
			],
			'defaults to bright' => [
				$createCallback(['test-dark.svg', 'test.svg']),
				null,
				'/path/test.svg',
			],
			'no dark icon on default' => [
				$createCallback(['test-dark.svg', 'test.svg', 'app-dark.svg', 'app.svg']),
				false,
				'/path/test.svg',
			],
			'no bright icon on dark' => [
				$createCallback(['test-dark.svg', 'test.svg', 'app-dark.svg', 'app.svg']),
				true,
				'/path/test-dark.svg',
			],
		];
	}

	public function testEnableApp() {
		// making sure "files_trashbin" is disabled
		if ($this->manager->isEnabledForUser('files_trashbin')) {
			$this->manager->disableApp('files_trashbin');
		}
		$this->eventDispatcher->expects($this->once())->method('dispatchTyped')->with(new AppEnableEvent('files_trashbin'));
		$this->manager->enableApp('files_trashbin');
		$this->assertEquals('yes', $this->appConfig->getValue('files_trashbin', 'enabled', 'no'));
	}

	public function testDisableApp() {
		$this->eventDispatcher->expects($this->once())->method('dispatchTyped')->with(new AppDisableEvent('files_trashbin'));
		$this->manager->disableApp('files_trashbin');
		$this->assertEquals('no', $this->appConfig->getValue('files_trashbin', 'enabled', 'no'));
	}

	public function testNotEnableIfNotInstalled() {
		try {
			$this->manager->enableApp('some_random_name_which_i_hope_is_not_an_app');
			$this->assertFalse(true, 'If this line is reached the expected exception is not thrown.');
		} catch (AppPathNotFoundException $e) {
			// Exception is expected
			$this->assertEquals('Could not find path for some_random_name_which_i_hope_is_not_an_app', $e->getMessage());
		}

		$this->assertEquals('no', $this->appConfig->getValue(
			'some_random_name_which_i_hope_is_not_an_app', 'enabled', 'no'
		));
	}

	public function testEnableAppForGroups() {
		$group1 = $this->createMock(IGroup::class);
		$group1->method('getGID')
			->willReturn('group1');
		$group2 = $this->createMock(IGroup::class);
		$group2->method('getGID')
			->willReturn('group2');

		$groups = [$group1, $group2];

		/** @var AppManager|MockObject $manager */
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppPath',
			])
			->getMock();

		$manager->expects($this->exactly(2))
			->method('getAppPath')
			->with('test')
			->willReturn('apps/test');

		$this->eventDispatcher->expects($this->once())->method('dispatchTyped')->with(new AppEnableEvent('test', ['group1', 'group2']));

		$manager->enableAppForGroups('test', $groups);
		$this->assertEquals('["group1","group2"]', $this->appConfig->getValue('test', 'enabled', 'no'));
	}

	public function dataEnableAppForGroupsAllowedTypes() {
		return [
			[[]],
			[[
				'types' => [],
			]],
			[[
				'types' => ['nickvergessen'],
			]],
		];
	}

	/**
	 * @dataProvider dataEnableAppForGroupsAllowedTypes
	 *
	 * @param array $appInfo
	 */
	public function testEnableAppForGroupsAllowedTypes(array $appInfo) {
		$group1 = $this->createMock(IGroup::class);
		$group1->method('getGID')
			->willReturn('group1');
		$group2 = $this->createMock(IGroup::class);
		$group2->method('getGID')
			->willReturn('group2');

		$groups = [$group1, $group2];

		/** @var AppManager|MockObject $manager */
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppPath',
				'getAppInfo',
			])
			->getMock();

		$manager->expects($this->once())
			->method('getAppPath')
			->with('test')
			->willReturn(null);

		$manager->expects($this->once())
			->method('getAppInfo')
			->with('test')
			->willReturn($appInfo);

		$this->eventDispatcher->expects($this->once())->method('dispatchTyped')->with(new AppEnableEvent('test', ['group1', 'group2']));

		$manager->enableAppForGroups('test', $groups);
		$this->assertEquals('["group1","group2"]', $this->appConfig->getValue('test', 'enabled', 'no'));
	}

	public function dataEnableAppForGroupsForbiddenTypes() {
		return [
			['filesystem'],
			['prelogin'],
			['authentication'],
			['logging'],
			['prevent_group_restriction'],
		];
	}

	/**
	 * @dataProvider dataEnableAppForGroupsForbiddenTypes
	 *
	 * @param string $type
	 *
	 */
	public function testEnableAppForGroupsForbiddenTypes($type) {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('test can\'t be enabled for groups.');

		$group1 = $this->createMock(IGroup::class);
		$group1->method('getGID')
			->willReturn('group1');
		$group2 = $this->createMock(IGroup::class);
		$group2->method('getGID')
			->willReturn('group2');

		$groups = [$group1, $group2];

		/** @var AppManager|MockObject $manager */
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppPath',
				'getAppInfo',
			])
			->getMock();

		$manager->expects($this->once())
			->method('getAppPath')
			->with('test')
			->willReturn(null);

		$manager->expects($this->once())
			->method('getAppInfo')
			->with('test')
			->willReturn([
				'types' => [$type],
			]);

		$this->eventDispatcher->expects($this->never())->method('dispatchTyped')->with(new AppEnableEvent('test', ['group1', 'group2']));

		$manager->enableAppForGroups('test', $groups);
	}

	public function testIsInstalledEnabled() {
		$this->appConfig->setValue('test', 'enabled', 'yes');
		$this->assertTrue($this->manager->isInstalled('test'));
	}

	public function testIsInstalledDisabled() {
		$this->appConfig->setValue('test', 'enabled', 'no');
		$this->assertFalse($this->manager->isInstalled('test'));
	}

	public function testIsInstalledEnabledForGroups() {
		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isInstalled('test'));
	}

	private function newUser($uid) {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')
			->willReturn($uid);

		return $user;
	}

	public function testIsEnabledForUserEnabled() {
		$this->appConfig->setValue('test', 'enabled', 'yes');
		$user = $this->newUser('user1');
		$this->assertTrue($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserDisabled() {
		$this->appConfig->setValue('test', 'enabled', 'no');
		$user = $this->newUser('user1');
		$this->assertFalse($this->manager->isEnabledForUser('test', $user));
	}

	public function testGetAppPath() {
		$this->assertEquals(\OC::$SERVERROOT . '/apps/files', $this->manager->getAppPath('files'));
	}

	public function testGetAppPathSymlink() {
		$fakeAppDirname = sha1(uniqid('test', true));
		$fakeAppPath = sys_get_temp_dir() . '/' . $fakeAppDirname;
		$fakeAppLink = \OC::$SERVERROOT . '/' . $fakeAppDirname;

		mkdir($fakeAppPath);
		if (symlink($fakeAppPath, $fakeAppLink) === false) {
			$this->markTestSkipped('Failed to create symlink');
		}

		// Use the symlink as the app path
		\OC::$APPSROOTS[] = [
			'path' => $fakeAppLink,
			'url' => \OC::$WEBROOT . '/' . $fakeAppDirname,
			'writable' => false,
		];

		$fakeTestAppPath = $fakeAppPath . '/' . 'test-test-app';
		mkdir($fakeTestAppPath);

		$generatedAppPath = $this->manager->getAppPath('test-test-app');

		rmdir($fakeTestAppPath);
		unlink($fakeAppLink);
		rmdir($fakeAppPath);

		$this->assertEquals($fakeAppLink . '/test-test-app', $generatedAppPath);
	}

	public function testGetAppPathFail() {
		$this->expectException(AppPathNotFoundException::class);
		$this->manager->getAppPath('testnotexisting');
	}

	public function testIsEnabledForUserEnabledForGroup() {
		$user = $this->newUser('user1');
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->willReturn(['foo', 'bar']);

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserDisabledForGroup() {
		$user = $this->newUser('user1');
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->willReturn(['bar']);

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertFalse($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserLoggedOut() {
		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertFalse($this->manager->isEnabledForUser('test'));
	}

	public function testIsEnabledForUserLoggedIn() {
		$user = $this->newUser('user1');

		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->willReturn(['foo', 'bar']);

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isEnabledForUser('test'));
	}

	public function testGetInstalledApps() {
		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');
		$apps = [
			'cloud_federation_api',
			'dav',
			'federatedfilesharing',
			'files',
			'lookup_server_connector',
			'oauth2',
			'provisioning_api',
			'settings',
			'test1',
			'test3',
			'theming',
			'twofactor_backupcodes',
			'viewer',
			'workflowengine',
		];
		$this->assertEquals($apps, $this->manager->getInstalledApps());
	}

	public function testGetAppsForUser() {
		$user = $this->newUser('user1');
		$this->groupManager->expects($this->any())
			->method('getUserGroupIds')
			->with($user)
			->willReturn(['foo', 'bar']);

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');
		$this->appConfig->setValue('test4', 'enabled', '["asd"]');
		$enabled = [
			'cloud_federation_api',
			'dav',
			'federatedfilesharing',
			'files',
			'lookup_server_connector',
			'oauth2',
			'provisioning_api',
			'settings',
			'test1',
			'test3',
			'theming',
			'twofactor_backupcodes',
			'viewer',
			'workflowengine',
		];
		$this->assertEquals($enabled, $this->manager->getEnabledAppsForUser($user));
	}

	public function testGetAppsNeedingUpgrade() {
		/** @var AppManager|MockObject $manager */
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods(['getAppInfo'])
			->getMock();

		$appInfos = [
			'cloud_federation_api' => ['id' => 'cloud_federation_api'],
			'dav' => ['id' => 'dav'],
			'files' => ['id' => 'files'],
			'federatedfilesharing' => ['id' => 'federatedfilesharing'],
			'provisioning_api' => ['id' => 'provisioning_api'],
			'lookup_server_connector' => ['id' => 'lookup_server_connector'],
			'test1' => ['id' => 'test1', 'version' => '1.0.1', 'requiremax' => '9.0.0'],
			'test2' => ['id' => 'test2', 'version' => '1.0.0', 'requiremin' => '8.2.0'],
			'test3' => ['id' => 'test3', 'version' => '1.2.4', 'requiremin' => '9.0.0'],
			'test4' => ['id' => 'test4', 'version' => '3.0.0', 'requiremin' => '8.1.0'],
			'testnoversion' => ['id' => 'testnoversion', 'requiremin' => '8.2.0'],
			'settings' => ['id' => 'settings'],
			'theming' => ['id' => 'theming'],
			'twofactor_backupcodes' => ['id' => 'twofactor_backupcodes'],
			'viewer' => ['id' => 'viewer'],
			'workflowengine' => ['id' => 'workflowengine'],
			'oauth2' => ['id' => 'oauth2'],
		];

		$manager->expects($this->any())
			->method('getAppInfo')
			->willReturnCallback(
				function ($appId) use ($appInfos) {
					return $appInfos[$appId];
				}
			);

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test1', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test2', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test3', 'enabled', 'yes');
		$this->appConfig->setValue('test3', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test4', 'enabled', 'yes');
		$this->appConfig->setValue('test4', 'installed_version', '2.4.0');

		$apps = $manager->getAppsNeedingUpgrade('8.2.0');

		$this->assertCount(2, $apps);
		$this->assertEquals('test1', $apps[0]['id']);
		$this->assertEquals('test4', $apps[1]['id']);
	}

	public function testGetIncompatibleApps() {
		/** @var AppManager|MockObject $manager */
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods(['getAppInfo'])
			->getMock();

		$appInfos = [
			'cloud_federation_api' => ['id' => 'cloud_federation_api'],
			'dav' => ['id' => 'dav'],
			'files' => ['id' => 'files'],
			'federatedfilesharing' => ['id' => 'federatedfilesharing'],
			'provisioning_api' => ['id' => 'provisioning_api'],
			'lookup_server_connector' => ['id' => 'lookup_server_connector'],
			'test1' => ['id' => 'test1', 'version' => '1.0.1', 'requiremax' => '8.0.0'],
			'test2' => ['id' => 'test2', 'version' => '1.0.0', 'requiremin' => '8.2.0'],
			'test3' => ['id' => 'test3', 'version' => '1.2.4', 'requiremin' => '9.0.0'],
			'settings' => ['id' => 'settings'],
			'testnoversion' => ['id' => 'testnoversion', 'requiremin' => '8.2.0'],
			'theming' => ['id' => 'theming'],
			'twofactor_backupcodes' => ['id' => 'twofactor_backupcodes'],
			'workflowengine' => ['id' => 'workflowengine'],
			'oauth2' => ['id' => 'oauth2'],
			'viewer' => ['id' => 'viewer'],
		];

		$manager->expects($this->any())
			->method('getAppInfo')
			->willReturnCallback(
				function ($appId) use ($appInfos) {
					return $appInfos[$appId];
				}
			);

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'yes');
		$this->appConfig->setValue('test3', 'enabled', 'yes');

		$apps = $manager->getIncompatibleApps('8.2.0');

		$this->assertCount(2, $apps);
		$this->assertEquals('test1', $apps[0]['id']);
		$this->assertEquals('test3', $apps[1]['id']);
	}

	public function testGetEnabledAppsForGroup() {
		$group = $this->createMock(IGroup::class);
		$group->expects($this->any())
			->method('getGID')
			->willReturn('foo');

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');
		$this->appConfig->setValue('test4', 'enabled', '["asd"]');
		$enabled = [
			'cloud_federation_api',
			'dav',
			'federatedfilesharing',
			'files',
			'lookup_server_connector',
			'oauth2',
			'provisioning_api',
			'settings',
			'test1',
			'test3',
			'theming',
			'twofactor_backupcodes',
			'viewer',
			'workflowengine',
		];
		$this->assertEquals($enabled, $this->manager->getEnabledAppsForGroup($group));
	}

	public function testGetAppRestriction() {
		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');

		$this->assertEquals([], $this->manager->getAppRestriction('test1'));
		$this->assertEquals([], $this->manager->getAppRestriction('test2'));
		$this->assertEquals(['foo'], $this->manager->getAppRestriction('test3'));
	}

	public function provideDefaultApps(): array {
		return [
			// none specified, default to files
			[
				'',
				'',
				'{}',
				true,
				'files',
			],
			// none specified, without fallback
			[
				'',
				'',
				'{}',
				false,
				'',
			],
			// unexisting or inaccessible app specified, default to files
			[
				'unexist',
				'',
				'{}',
				true,
				'files',
			],
			// unexisting or inaccessible app specified, without fallbacks
			[
				'unexist',
				'',
				'{}',
				false,
				'',
			],
			// non-standard app
			[
				'settings',
				'',
				'{}',
				true,
				'settings',
			],
			// non-standard app, without fallback
			[
				'settings',
				'',
				'{}',
				false,
				'settings',
			],
			// non-standard app with fallback
			[
				'unexist,settings',
				'',
				'{}',
				true,
				'settings',
			],
			// system default app and user apporder
			[
				// system default is settings
				'unexist,settings',
				'',
				// apporder says default app is files (order is lower)
				'{"files_id":{"app":"files","order":1},"settings_id":{"app":"settings","order":2}}',
				true,
				// system default should override apporder
				'settings'
			],
			// user-customized defaultapp
			[
				'',
				'files',
				'',
				true,
				'files',
			],
			// user-customized defaultapp with systemwide
			[
				'unexist,settings',
				'files',
				'',
				true,
				'files',
			],
			// user-customized defaultapp with system wide and apporder
			[
				'unexist,settings',
				'files',
				'{"settings_id":{"app":"settings","order":1},"files_id":{"app":"files","order":2}}',
				true,
				'files',
			],
			// user-customized apporder fallback
			[
				'',
				'',
				'{"settings_id":{"app":"settings","order":1},"files":{"app":"files","order":2}}',
				true,
				'settings',
			],
			// user-customized apporder fallback with missing app key (entries added by closures does not always have an app key set (Nextcloud 27 spreed app for example))
			[
				'',
				'',
				'{"spreed":{"order":1},"files":{"app":"files","order":2}}',
				true,
				'files',
			],
			// user-customized apporder, but called without fallback
			[
				'',
				'',
				'{"settings":{"app":"settings","order":1},"files":{"app":"files","order":2}}',
				false,
				'',
			],
			// user-customized apporder with an app that has multiple routes
			[
				'',
				'',
				'{"settings_id":{"app":"settings","order":1},"settings_id_2":{"app":"settings","order":3},"id_files":{"app":"files","order":2}}',
				true,
				'settings',
			],
		];
	}

	/**
	 * @dataProvider provideDefaultApps
	 */
	public function testGetDefaultAppForUser($defaultApps, $userDefaultApps, $userApporder, $withFallbacks, $expectedApp) {
		$user = $this->newUser('user1');

		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$this->config->expects($this->once())
			->method('getSystemValueString')
			->with('defaultapp', $this->anything())
			->willReturn($defaultApps);

		$this->config->expects($this->atLeastOnce())
			->method('getUserValue')
			->willReturnMap([
				['user1', 'core', 'defaultapp', '', $userDefaultApps],
				['user1', 'core', 'apporder', '[]', $userApporder],
			]);

		$this->assertEquals($expectedApp, $this->manager->getDefaultAppForUser(null, $withFallbacks));
	}

	public static function isBackendRequiredDataProvider(): array {
		return [
			// backend available
			[
				'caldav',
				['app1' => ['caldav']],
				true,
			],
			[
				'caldav',
				['app1' => [], 'app2' => ['foo'], 'app3' => ['caldav']],
				true,
			],
			// backend not available
			[
				'caldav',
				['app3' => [], 'app1' => ['foo'], 'app2' => ['bar', 'baz']],
				false,
			],
			// no app available
			[
				'caldav',
				[],
				false,
			],
		];
	}

	/**
	 * @dataProvider isBackendRequiredDataProvider
	 */
	public function testIsBackendRequired(
		string $backend,
		array $appBackends,
		bool $expected,
	): void {
		$appInfoData = array_map(
			static fn (array $backends) => ['dependencies' => ['backend' => $backends]],
			$appBackends,
		);

		$reflection = new \ReflectionClass($this->manager);
		$property = $reflection->getProperty('appInfos');
		$property->setValue($this->manager, $appInfoData);

		$this->assertEquals($expected, $this->manager->isBackendRequired($backend));
	}

	public function testGetAppVersion() {
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppInfo',
			])
			->getMock();

		$manager->expects(self::once())
			->method('getAppInfo')
			->with('myapp')
			->willReturn(['version' => '99.99.99-rc.99']);

		$this->assertEquals(
			'99.99.99-rc.99',
			$manager->getAppVersion('myapp'),
		);
	}

	public function testGetAppVersionCore() {
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppInfo',
			])
			->getMock();

		$manager->expects(self::never())
			->method('getAppInfo');

		$util = new \OC_Util();
		self::invokePrivate($util, 'versionCache', [['OC_VersionString' => '1.2.3-beta.4']]);

		$this->assertEquals(
			'1.2.3-beta.4',
			$manager->getAppVersion('core'),
		);
	}

	public function testGetAppVersionUnknown() {
		$manager = $this->getMockBuilder(AppManager::class)
			->setConstructorArgs([
				$this->userSession,
				$this->config,
				$this->groupManager,
				$this->cacheFactory,
				$this->eventDispatcher,
				$this->logger,
			])
			->onlyMethods([
				'getAppInfo',
			])
			->getMock();

		$manager->expects(self::once())
			->method('getAppInfo')
			->with('unknown')
			->willReturn(null);

		$this->assertEquals(
			'0',
			$manager->getAppVersion('unknown'),
		);
	}

}
