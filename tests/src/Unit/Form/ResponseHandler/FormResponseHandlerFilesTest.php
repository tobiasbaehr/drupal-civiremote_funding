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

namespace Drupal\Tests\civiremote_funding\Unit\Form\ResponseHandler;

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerFiles;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use Drupal\Tests\civiremote_funding\Unit\File\Entity\FundingFileMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

/**
 * @covers \Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerFiles
 * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
 */
final class FormResponseHandlerFilesTest extends TestCase {

  /**
   * @var \Drupal\Core\Form\FormStateInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formStateMock;

  /**
   * @var \Drupal\civiremote_funding\File\FundingFileManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingFileManagerMock;

  private FormResponseHandlerFiles $handler;

  private TestLogger $logger;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingFileManagerMock = $this->createMock(FundingFileManager::class);
    $this->logger = new TestLogger();
    $this->handler = new FormResponseHandlerFiles(
      $this->fundingFileManagerMock,
      $this->logger,
    );
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  public function testFileUnchanged(): void {
    $files = ['https://unchanged' => 'https://unchanged'];
    $submitResponse = new FormSubmitResponse('closeForm', NULL, [], NULL, $files);

    $this->fundingFileManagerMock->expects(static::never())->method('save');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testFileSubmitted(): void {
    $files = ['https://submitted' => 'https://civicrm'];
    $submitResponse = new FormSubmitResponse('closeForm', NULL, [], NULL, $files);

    $fileMock = $this->createMock(FileInterface::class);
    $fileMock->method('id')->willReturn('2');
    $fundingFile = new FundingFileMock($fileMock);

    $filesProperty = [
      '2' => [
        'uri' => 'https://submitted',
        'fundingFile' => $fundingFile,
      ],
    ];
    $this->formStateMock->method('get')->with('files')->willReturn($filesProperty);

    $this->fundingFileManagerMock->expects(static::once())->method('save')->with($fundingFile);
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
    static::assertSame('https://civicrm', $fundingFile->getCiviUri());
  }

  public function testFileSubmittedNotFound(): void {
    $files = ['https://returned-by-civicrm' => 'https://civicrm'];
    $submitResponse = new FormSubmitResponse('closeForm', NULL, [], NULL, $files);

    $fileMock = $this->createMock(FileInterface::class);
    $fileMock->method('id')->willReturn('2');
    $fundingFile = new FundingFileMock($fileMock);

    $filesProperty = [
      '2' => [
        'uri' => 'https://submitted',
        'fundingFile' => $fundingFile,
      ],
    ];
    $this->formStateMock->method('get')->with('files')->willReturn($filesProperty);

    $this->fundingFileManagerMock->expects(static::never())->method('save');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
    static::assertTrue($this->logger->hasError('No funding file found for "https://returned-by-civicrm"'));
  }

}
