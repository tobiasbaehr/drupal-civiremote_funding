<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\Tests\civiremote_funding\Unit\File;

use Drupal\civiremote_funding\File\FundingFileDownloader;
use Drupal\civiremote_funding\File\FundingFileDownloadHook;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\file\FileInterface;
use Drupal\Tests\civiremote_funding\Unit\File\Entity\FundingFileMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Drupal\civiremote_funding\File\FundingFileDownloadHook
 */
final class FundingFileDownloadHookTest extends TestCase {

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileDownloader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingFileDownloaderMock;

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private $fundingFileManagerMock;

  private FundingFileDownloadHook $hook;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(FundingFileDownloadHook::class);
    ClockMock::withClockMock(1234);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->fundingFileDownloaderMock = $this->createMock(FundingFileDownloader::class);
    $this->fundingFileManagerMock = $this->createMock(FundingFileManager::class);
    $this->hook = new FundingFileDownloadHook(
      $this->fundingFileDownloaderMock,
      $this->fundingFileManagerMock
    );
  }

  public function testNoFundingFile(): void {
    $this->fundingFileManagerMock->method('loadByFileUri')
      ->with('public://test')
      ->willReturn(NULL);

    static::assertSame([], ($this->hook)('public://test'));
  }

  public function testDownload(): void {
    $fileMock = $this->createMock(FileInterface::class);
    $fileMock->method('getMimeType')->willReturn('media/type');

    $fundingFile = new FundingFileMock($fileMock);
    $this->fundingFileManagerMock->method('loadByFileUri')
      ->with('public://test')
      ->willReturn($fundingFile);

    $this->fundingFileDownloaderMock->expects(static::once())->method('download')
      ->with($fundingFile);

    $this->fundingFileManagerMock->expects(static::once())->method('save')
      ->with($fundingFile);

    $expectedHeaders = [
      'Content-Type' => 'media/type',
      'Cache-Control' => 'private',
    ];
    static::assertSame($expectedHeaders, ($this->hook)('public://test'));

    static::assertSame(1234, $fundingFile->getLastAccess());
  }

}
