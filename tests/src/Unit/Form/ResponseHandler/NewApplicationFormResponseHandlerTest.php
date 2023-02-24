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
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerAction;
use Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerFiles;
use Drupal\civiremote_funding\Form\ResponseHandler\NewApplicationFormResponseHandler;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\civiremote_funding\Form\ResponseHandler\NewApplicationFormResponseHandler
 * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
 */
final class NewApplicationFormResponseHandlerTest extends TestCase {

  /**
   * @var \Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerAction&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formResponseHandlerActionMock;

  /**
   * @var \Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerFiles&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formResponseHandlerFilesMock;

  /**
   * @var \Drupal\Core\Form\FormStateInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formStateMock;

  private NewApplicationFormResponseHandler $handler;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $messengerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formResponseHandlerActionMock = $this->createMock(FormResponseHandlerAction::class);
    $this->formResponseHandlerFilesMock = $this->createMock(FormResponseHandlerFiles::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->handler = new NewApplicationFormResponseHandler(
      $this->formResponseHandlerActionMock,
      $this->formResponseHandlerFilesMock,
      $this->messengerMock,
    );
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  public function testRedirect(): void {
    $jsonSchema = new \stdClass();
    $jsonSchema->a = 'b';
    $uiSchema = new \stdClass();
    $uiSchema->x = 'y';
    $form = new FundingForm($jsonSchema, $uiSchema, ['applicationProcessId' => 1234]);

    $submitResponse = new FormSubmitResponse('showForm', 'Test', [], $form, []);

    $this->formResponseHandlerFilesMock->expects(static::once())->method('handleSubmitResponse')
      ->with($submitResponse);
    $this->formResponseHandlerActionMock->expects(static::never())->method('handleSubmitResponse');
    $this->messengerMock->expects(static::once())->method('addMessage')
      ->with('Test');
    $this->formStateMock->expects(static::once())->method('setRedirect')
      ->with('civiremote_funding.application_form', [
        'applicationProcessId' => 1234,
      ]);

    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testNoRedirect(): void {
    $jsonSchema = new \stdClass();
    $jsonSchema->a = 'b';
    $uiSchema = new \stdClass();
    $uiSchema->x = 'y';
    $form = new FundingForm($jsonSchema, $uiSchema, ['applicationProcessId' => 1234]);

    $submitResponse = new FormSubmitResponse('someAction', 'Test', [], $form, []);

    $this->formResponseHandlerFilesMock->expects(static::once())->method('handleSubmitResponse')
      ->with($submitResponse);
    $this->formResponseHandlerActionMock->expects(static::once())->method('handleSubmitResponse')
      ->with($submitResponse);
    $this->messengerMock->expects(static::never())->method('addMessage');
    $this->formStateMock->expects(static::never())->method('setRedirect');

    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

}
