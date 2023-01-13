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
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerAction
 * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
 * @covers \Drupal\civiremote_funding\Api\Form\FundingForm
 */
final class FormResponseHandlerActionTest extends TestCase {

  /**
   * @var \Drupal\Core\Form\FormStateInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formStateMock;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $messengerMock;

  private FormResponseHandlerAction $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->handler = new FormResponseHandlerAction($this->messengerMock);
    $this->handler->setStringTranslation($this->createMock(TranslationInterface::class));
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  public function testShowValidation(): void {
    $submitResponse = new FormSubmitResponse('showValidation', 'Test', [], NULL, []);

    $this->messengerMock->expects(static::once())->method('addWarning')->with('Test');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testShowValidationWithoutMessage(): void {
    $submitResponse = new FormSubmitResponse('showValidation', NULL, [], NULL, []);

    $this->messengerMock->expects(static::once())->method('addWarning')->with(static::callback(
      function (TranslatableMarkup $value) {
        static::assertSame('Validation failed.', $value->getUntranslatedString());

        return TRUE;
      }
    ));
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testCloseForm(): void {
    $submitResponse = new FormSubmitResponse('closeForm', 'Test', [], NULL, []);

    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setRedirect')->with('<front>');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testShowForm(): void {
    $jsonSchema = new \stdClass();
    $jsonSchema->a = 'b';
    $uiSchema = new \stdClass();
    $uiSchema->x = 'y';
    $form = new FundingForm($jsonSchema, $uiSchema, ['foo' => 'bar']);
    $submitResponse = new FormSubmitResponse('showForm', 'Test', [], $form, []);

    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::exactly(2))->method('set')->withConsecutive(
      ['jsonSchema', $jsonSchema],
      ['uiSchema', $uiSchema],
    );
    $this->formStateMock->expects(static::once())->method('setTemporary')->with(['foo' => 'bar']);
    $this->formStateMock->expects(static::once())->method('setRebuild');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testUnknownAction(): void {
    $submitResponse = new FormSubmitResponse('someAction', NULL, [], NULL, []);

    $this->messengerMock->expects(static::never())->method('addMessage');
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Unknown response action "someAction"');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

}
