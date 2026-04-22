<?php

<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> ionos-dev
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Trashbin\Tests\Sabre;

use OC\Files\FileInfo;
use OC\Files\View;
use OCA\Files_Trashbin\Sabre\ITrash;
use OCA\Files_Trashbin\Sabre\RestoreFolder;
use OCA\Files_Trashbin\Sabre\TrashbinPlugin;
use OCA\Files_Trashbin\Trash\ITrashItem;
use OCP\IPreview;
use Sabre\DAV\Server;
use Sabre\DAV\Tree;
use Test\TestCase;

class TrashbinPluginTest extends TestCase {
	private Server $server;

	protected function setUp(): void {
		parent::setUp();

		$tree = $this->createMock(Tree::class);
		$this->server = new Server($tree);
	}

<<<<<<< HEAD
	#[\PHPUnit\Framework\Attributes\DataProvider('quotaProvider')]
	public function testQuota(int $quota, int $fileSize, bool $expectedResult): void {
		$fileInfo = $this->createMock(ITrashItem::class);
		$fileInfo->method('getSize')
			->willReturn($fileSize);

		$trashNode = $this->createMock(ITrash::class);
		$trashNode->method('getFileInfo')
			->willReturn($fileInfo);

		$restoreNode = $this->createMock(RestoreFolder::class);

		$this->server->tree->method('getNodeForPath')
			->willReturn($trashNode, $restoreNode);
=======
	/**
	 * @dataProvider quotaProvider
	 */
	public function testQuota(int $quota, int $fileSize, bool $expectedResult): void {
		$fileInfo = $this->createMock(ITrashItem::class);
		$fileInfo->method('getSize')->willReturn($fileSize);

		$trashNode = $this->createMock(ITrash::class);
		$trashNode->method('getFileInfo')->willReturn($fileInfo);

		$restoreNode = $this->createMock(RestoreFolder::class);

		$this->server->tree->method('getNodeForPath')->willReturn($trashNode, $restoreNode);
>>>>>>> ionos-dev

		$previewManager = $this->createMock(IPreview::class);

		$view = $this->createMock(View::class);
<<<<<<< HEAD
		$view->method('free_space')
			->willReturn($quota);
=======
		$view->method('free_space')->willReturn($quota);
>>>>>>> ionos-dev

		$plugin = new TrashbinPlugin($previewManager, $view);
		$plugin->initialize($this->server);

		$sourcePath = 'trashbin/test/trash/file1';
		$destinationPath = 'trashbin/test/restore/file1';
		$this->assertEquals($expectedResult, $plugin->beforeMove($sourcePath, $destinationPath));
	}

<<<<<<< HEAD
	public static function quotaProvider(): array {
=======
	public function quotaProvider(): array {
>>>>>>> ionos-dev
		return [
			[ 1024, 512, true ],
			[ 512, 513, false ],
			[ FileInfo::SPACE_NOT_COMPUTED, 1024, true ],
			[ FileInfo::SPACE_UNKNOWN, 1024, true ],
			[ FileInfo::SPACE_UNLIMITED, 1024, true ]
		];
	}
}
