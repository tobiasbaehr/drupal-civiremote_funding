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

namespace Drupal\Tests\civiremote_funding\Unit\Controller;

use Assert\Assertion;
use Drupal\civiremote_funding\Controller\TokenFileDownloadController;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\file\FileInterface;
use Drupal\Tests\civiremote_funding\Unit\File\Entity\FundingFileMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Drupal\civiremote_funding\Controller\TokenFileDownloadController
 */
final class TokenFileDownloadControllerTest extends TestCase {

  private TokenFileDownloadController $controller;

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingFileManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingFileManagerMock = $this->createMock(FundingFileManager::class);
    $this->controller = new TokenFileDownloadController($this->fundingFileManagerMock);
  }

  public function testDownload(): void {
    $tmpFile = tempnam(sys_get_temp_dir(), 'civiremote_funding');
    Assertion::string($tmpFile);

    try {
      file_put_contents($tmpFile, 'test');
      $fileMock = $this->createMock(FileInterface::class);
      $fileMock->method('getFileUri')->willReturn('file://' . $tmpFile);
      $fileMock->method('getMimeType')->willReturn('media/type');
      $fundingFile = new FundingFileMock($fileMock);

      $this->fundingFileManagerMock->expects(static::once())->method('loadByTokenAndFilename')
        ->with('token', 'filename')
        ->willReturn($fundingFile);
      $response = $this->controller->download('token', 'filename');
      static::assertSame(200, $response->getStatusCode());
      static::assertSame('media/type', $response->headers->get('Content-Type'));

      ob_start();
      $response->sendContent();
      $content = ob_get_contents();
      ob_end_clean();
      static::assertSame('test', $content);
    }
    finally {
      unlink($tmpFile);
    }
  }

  public function testDownloadUnknownToken(): void {
    $this->fundingFileManagerMock->expects(static::once())->method('loadByTokenAndFilename')
      ->with('token', 'filename')
      ->willReturn(NULL);
    $response = $this->controller->download('token', 'filename');
    static::assertSame(404, $response->getStatusCode());
  }

  public function testDownloadFileNotExists(): void {
    $fileMock = $this->createMock(FileInterface::class);
    $fileMock->method('getFileUri')->willReturn('file://unavailable');
    $fundingFile = new FundingFileMock($fileMock);

    $this->fundingFileManagerMock->expects(static::once())->method('loadByTokenAndFilename')
      ->with('token', 'filename')
      ->willReturn($fundingFile);
    $this->expectException(NotFoundHttpException::class);
    $this->controller->download('token', 'filename');
  }

}
