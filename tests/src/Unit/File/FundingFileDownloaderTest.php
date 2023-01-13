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

use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\civiremote_funding\File\FundingFileDownloader;
use Drupal\civiremote_funding\File\FundingFileHttpClient;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileStorageInterface;
use Drupal\Tests\civiremote_funding\Unit\File\Entity\FundingFileMock;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * @covers \Drupal\civiremote_funding\File\FundingFileDownloader
 */
final class FundingFileDownloaderTest extends TestCase {

  private FundingFileDownloader $downloader;

  /**
   * @var \Drupal\file\FileRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fileRepositoryMock;

  /**
   * @var \Drupal\file\FileStorageInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fileStorageMock;

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileHttpClient&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingFileHttpClientMock;

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingFileManagerMock;

  /**
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $mimeTypeGuesserMock;

  private FundingFileInterface $fundingFile;

  private TestLogger $logger;

  /**
   * @var \Drupal\file\FileInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fileMock;

  protected function setUp(): void {
    parent::setUp();
    $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);
    $this->fileStorageMock = $this->createMock(FileStorageInterface::class);
    $this->fundingFileHttpClientMock = $this->createMock(FundingFileHttpClient::class);
    $this->fundingFileManagerMock = $this->createMock(FundingFileManager::class);
    $this->logger = new TestLogger();
    $this->mimeTypeGuesserMock = $this->createMock(MimeTypeGuesserInterface::class);
    $this->downloader = new FundingFileDownloader(
      $this->fileRepositoryMock,
      $this->fileStorageMock,
      $this->fundingFileHttpClientMock,
      $this->fundingFileManagerMock,
      $this->logger,
      $this->mimeTypeGuesserMock,
    );

    $this->fileMock = $this->createMock(FileInterface::class);
    $this->fileMock->method('id')->willReturn('2');
    $this->fileMock->method('getFileUri')->willReturn('public://test');
    $this->fundingFile = (new FundingFileMock($this->fileMock))
      ->setCiviUri('https://example.org/civicrm/download/1234');
  }

  public function testDownloadNotFound(): void {
    $this->mockResponse(404);
    $this->expectException(NotFoundHttpException::class);
    $this->downloader->download($this->fundingFile);
  }

  public function testDownloadUnauthorized(): void {
    $this->mockResponse(401);
    $e = NULL;
    try {
      $this->downloader->download($this->fundingFile);
    }
    catch (ServiceUnavailableHttpException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);

    static::assertTrue($this->logger->hasError([
      'message' => 'Authentication at CiviCRM failed',
      'context' => [
        'uri' => 'https://example.org/civicrm/download/1234',
      ],
    ]));
  }

  public function testDownloadForbidden(): void {
    $this->mockResponse(403);
    $this->expectException(AccessDeniedHttpException::class);
    $this->downloader->download($this->fundingFile);
  }

  public function testDownloadCached(): void {
    $this->mockResponse(304);
    $this->fileRepositoryMock->expects(static::never())->method('writeData');
    $this->downloader->download($this->fundingFile);
  }

  public function testDownloadOk(): void {
    $this->mockResponse(200, ['Last-Modified' => 'Thu, 01 Jan 1970 01:02:03 GMT'], 'foo');

    $this->fileRepositoryMock->expects(static::once())->method('writeData')
      ->with('foo', 'public://test', FileSystemInterface::EXISTS_REPLACE)
      ->willReturn($this->fileMock);
    $this->mimeTypeGuesserMock->method('guessMimeType')
      ->with('public://test')
      ->willReturn('media/type');
    $this->fileMock->expects(static::once())->method('setMimeType')->with('media/type');
    $this->fileStorageMock->expects(static::once())->method('save')->with($this->fileMock);

    $this->fundingFileManagerMock->expects(static::once())->method('save')->with($this->fundingFile);

    $this->downloader->download($this->fundingFile);
    static::assertSame('Thu, 01 Jan 1970 01:02:03 GMT', $this->fundingFile->getLastModified());
  }

  public function testUnexpectedStatus(): void {
    $this->mockResponse(100, [], NULL, 'Test');

    $e = NULL;
    try {
      $this->downloader->download($this->fundingFile);
    }
    catch (ServiceUnavailableHttpException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);

    static::assertTrue($this->logger->hasError([
      'message' => 'Unexpected response while downloading file from CiviCRM',
      'context' => [
        'uri' => 'https://example.org/civicrm/download/1234',
        'statusCode' => 100,
        'reasonPhrase' => 'Test',
      ],
    ]));
  }

  public function testGuzzleException(): void {
    $guzzleException = new TransferException('Test', 10);
    $this->fundingFileHttpClientMock->expects(static::once())->method('get')
      ->with($this->fundingFile)
      ->willThrowException($guzzleException);

    $e = NULL;
    try {
      $this->downloader->download($this->fundingFile);
    }
    catch (ServiceUnavailableHttpException $e) {
      static::assertSame($guzzleException, $e->getPrevious());
      static::assertSame(10, $e->getCode());
    }
    static::assertNotNull($e);

    static::assertTrue($this->logger->hasError([
      'message' => 'Downloading file from CiviCRM failed: Test',
      'context' => [
        'uri' => 'https://example.org/civicrm/download/1234',
      ],
    ]));
  }

  /**
   * @param array<string, array<string>|string> $headers
   */
  private function mockResponse(
    int $status,
    array $headers = [],
    ?string $body = NULL,
    ?string $reason = NULL
  ): void {
    $this->fundingFileHttpClientMock->expects(static::once())->method('get')
      ->with($this->fundingFile)
      ->willReturn(new Response($status, $headers, $body, '1.1', $reason));
  }

}
