<?php

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\Files\Controller;

use OCA\Files\Service\TagService;
use OCA\Files\Service\UserConfig;
use OCA\Files\Service\ViewConfig;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\Files\Storage\ISharedStorage;
use OCP\Files\Storage\IStorage;
use OCP\Files\StorageNotAvailableException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IPreview;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Share\IAttributes;
use OCP\Share\IManager;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class ApiController
 *
 * @package OCA\Files\Controller
 */
class ApiControllerTest extends TestCase {
	/** @var string */
	private $appName = 'files';
	/** @var \OCP\IUser */
	private $user;
	/** @var IRequest */
	private $request;
	/** @var TagService */
	private $tagService;
	/** @var IPreview|\PHPUnit\Framework\MockObject\MockObject */
	private $preview;
	/** @var ApiController */
	private $apiController;
	/** @var \OCP\Share\IManager */
	private $shareManager;
	/** @var \OCP\IConfig */
	private $config;
	/** @var Folder|\PHPUnit\Framework\MockObject\MockObject */
	private $userFolder;
	/** @var UserConfig|\PHPUnit\Framework\MockObject\MockObject */
	private $userConfig;
	/** @var ViewConfig|\PHPUnit\Framework\MockObject\MockObject */
	private $viewConfig;
	/** @var IL10N|\PHPUnit\Framework\MockObject\MockObject */
	private $l10n;
	/** @var IRootFolder|\PHPUnit\Framework\MockObject\MockObject */
	private $rootFolder;
	/** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
	private $logger;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->getMockBuilder(IRequest::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user = $this->createMock(IUser::class);
		$this->user->expects($this->any())
			->method('getUID')
			->willReturn('user1');
		$userSession = $this->createMock(IUserSession::class);
		$userSession->expects($this->any())
			->method('getUser')
			->willReturn($this->user);
		$this->tagService = $this->getMockBuilder(TagService::class)
			->disableOriginalConstructor()
			->getMock();
		$this->shareManager = $this->getMockBuilder(IManager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->preview = $this->getMockBuilder(IPreview::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->createMock(IConfig::class);
		$this->userFolder = $this->getMockBuilder(Folder::class)
			->disableOriginalConstructor()
			->getMock();
		$this->userConfig = $this->createMock(UserConfig::class);
		$this->viewConfig = $this->createMock(ViewConfig::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->rootFolder = $this->createMock(IRootFolder::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->apiController = new ApiController(
			$this->appName,
			$this->request,
			$userSession,
			$this->tagService,
			$this->preview,
			$this->shareManager,
			$this->config,
			$this->userFolder,
			$this->userConfig,
			$this->viewConfig,
			$this->l10n,
			$this->rootFolder,
			$this->logger,
		);
	}

	public function testUpdateFileTagsEmpty() {
		$expected = new DataResponse([]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt'));
	}

	public function testUpdateFileTagsWorking() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2']);

		$expected = new DataResponse([
			'tags' => [
				'Tag1',
				'Tag2'
			],
		]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsNotFoundException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new NotFoundException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageNotAvailableException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new StorageNotAvailableException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_SERVICE_UNAVAILABLE);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageGenericException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new \Exception('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testGetThumbnailInvalidSize() {
		$this->userFolder->method('get')
			->with($this->equalTo(''))
			->willThrowException(new NotFoundException());
		$expected = new DataResponse(['message' => 'Requested size must be numeric and a positive value.'], Http::STATUS_BAD_REQUEST);
		$this->assertEquals($expected, $this->apiController->getThumbnail(0, 0, ''));
	}

	public function testGetThumbnailInvalidImage(): void {
		$storage = $this->createMock(IStorage::class);
		$storage->method('instanceOfStorage')->with(ISharedStorage::class)->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(123);
		$file->method('getStorage')->willReturn($storage);
		$this->userFolder->method('get')
			->with($this->equalTo('unknown.jpg'))
			->willReturn($file);
		$this->preview->expects($this->once())
			->method('getPreview')
			->with($file, 10, 10, true)
			->willThrowException(new NotFoundException());
		$expected = new DataResponse(['message' => 'File not found.'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->getThumbnail(10, 10, 'unknown.jpg'));
	}

	public function testGetThumbnailInvalidPartFile() {
		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(0);
		$this->userFolder->method('get')
			->with($this->equalTo('unknown.jpg'))
			->willReturn($file);
		$expected = new DataResponse(['message' => 'File not found.'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->getThumbnail(10, 10, 'unknown.jpg'));
	}

	public function testGetThumbnailSharedNoDownload(): void {
		$attributes = $this->createMock(IAttributes::class);
		$attributes->expects(self::once())
			->method('getAttribute')
			->with('permissions', 'download')
			->willReturn(false);

		$share = $this->createMock(IShare::class);
		$share->expects(self::once())
			->method('getAttributes')
			->willReturn($attributes);

		$storage = $this->createMock(ISharedStorage::class);
		$storage->expects(self::once())
			->method('instanceOfStorage')
			->with(ISharedStorage::class)
			->willReturn(true);
		$storage->expects(self::once())
			->method('getShare')
			->willReturn($share);

		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(123);
		$file->method('getStorage')->willReturn($storage);

		$this->userFolder->method('get')
			->with('unknown.jpg')
			->willReturn($file);

		$this->preview->expects($this->never())
			->method('getPreview');

		$expected = new DataResponse(['message' => 'File not found.'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->getThumbnail(10, 10, 'unknown.jpg'));
	}

	public function testGetThumbnailShared(): void {
		$share = $this->createMock(IShare::class);
		$share->expects(self::once())
			->method('getAttributes')
			->willReturn(null);

		$storage = $this->createMock(ISharedStorage::class);
		$storage->expects(self::once())
			->method('instanceOfStorage')
			->with(ISharedStorage::class)
			->willReturn(true);
		$storage->expects(self::once())
			->method('getShare')
			->willReturn($share);

		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(123);
		$file->method('getStorage')->willReturn($storage);

		$this->userFolder->method('get')
			->with($this->equalTo('known.jpg'))
			->willReturn($file);
		$preview = $this->createMock(ISimpleFile::class);
		$preview->method('getName')->willReturn('my name');
		$preview->method('getMTime')->willReturn(42);
		$this->preview->expects($this->once())
			->method('getPreview')
			->with($this->equalTo($file), 10, 10, true)
			->willReturn($preview);

		$ret = $this->apiController->getThumbnail(10, 10, 'known.jpg');

		$this->assertEquals(Http::STATUS_OK, $ret->getStatus());
		$this->assertInstanceOf(Http\FileDisplayResponse::class, $ret);
	}

	public function testGetThumbnail(): void {
		$storage = $this->createMock(IStorage::class);
		$storage->method('instanceOfStorage')->with(ISharedStorage::class)->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(123);
		$file->method('getStorage')->willReturn($storage);

		$this->userFolder->method('get')
			->with($this->equalTo('known.jpg'))
			->willReturn($file);
		$preview = $this->createMock(ISimpleFile::class);
		$preview->method('getName')->willReturn('my name');
		$preview->method('getMTime')->willReturn(42);
		$this->preview->expects($this->once())
			->method('getPreview')
			->with($this->equalTo($file), 10, 10, true)
			->willReturn($preview);

		$ret = $this->apiController->getThumbnail(10, 10, 'known.jpg');

		$this->assertEquals(Http::STATUS_OK, $ret->getStatus());
		$this->assertInstanceOf(Http\FileDisplayResponse::class, $ret);
	}

	public function testShowHiddenFiles() {
		$show = false;

		$this->config->expects($this->once())
			->method('setUserValue')
			->with($this->user->getUID(), 'files', 'show_hidden', '0');

		$expected = new Http\Response();
		$actual = $this->apiController->showHiddenFiles($show);

		$this->assertEquals($expected, $actual);
	}

	public function testCropImagePreviews() {
		$crop = true;

		$this->config->expects($this->once())
			->method('setUserValue')
			->with($this->user->getUID(), 'files', 'crop_image_previews', '1');

		$expected = new Http\Response();
		$actual = $this->apiController->cropImagePreviews($crop);

		$this->assertEquals($expected, $actual);
	}
}
