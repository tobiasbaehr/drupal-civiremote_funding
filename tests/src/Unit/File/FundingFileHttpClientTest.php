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

namespace Drupal\Tests\civiremote_funding\File;

use Drupal\civiremote_funding\File\FundingFileHttpClient;
use Drupal\civiremote_funding\RemotePage\RemotePageClient;
use Drupal\file\FileInterface;
use Drupal\Tests\civiremote_funding\Unit\File\Entity\FundingFileMock;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\civiremote_funding\File\FundingFileHttpClient
 */
final class FundingFileHttpClientTest extends TestCase {

  private FundingFileHttpClient $fundingFileHttpClient;

  /**
   * @var \Drupal\civiremote_funding\RemotePage\RemotePageClient&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $remotePageClientMock;

  protected function setUp(): void {
    parent::setUp();
    $this->remotePageClientMock = $this->createMock(RemotePageClient::class);
    $this->fundingFileHttpClient = new FundingFileHttpClient($this->remotePageClientMock);
  }

  public function testGet(): void {
    $remoteResponse = new Response();
    $this->remotePageClientMock->method('request')
      ->with('GET', 'https://example.org/file', [
        'headers' => [
          'If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT',
        ],
      ])->willReturn($remoteResponse);

    $fileMock = $this->createMock(FileInterface::class);
    $fundingFile = new FundingFileMock($fileMock);
    $fundingFile->setCiviUri('https://example.org/file');
    static::assertSame($remoteResponse, $this->fundingFileHttpClient->get($fundingFile));
  }

}
