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
use Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerAction;
use Drupal\civiremote_funding\FundingRedirectResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerAction
 * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
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

  /**
   * @var \Drupal\Core\Http\RequestStack&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $requestStackMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(Request::class);
    ClockMock::withClockMock(123456);
  }

  public static function tearDownAfterClass(): void {
    parent::tearDownAfterClass();
    ClockMock::withClockMock(FALSE);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->handler = new FormResponseHandlerAction($this->messengerMock, $this->requestStackMock);
    $this->handler->setStringTranslation($this->createMock(TranslationInterface::class));
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  public function testShowValidation(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'showValidation',
      'message' => 'Test',
    ]);

    $this->messengerMock->expects(static::once())->method('addWarning')->with('Test');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testShowValidationWithoutMessage(): void {
    $submitResponse = new FormSubmitResponse(['action' => 'showValidation']);

    $this->messengerMock->expects(static::once())->method('addWarning')->with(static::callback(
      function (TranslatableMarkup $value) {
        static::assertSame('Validation failed.', $value->getUntranslatedString());

        return TRUE;
      }
    ));
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testCloseForm(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'closeForm',
      'message' => 'Test',
    ]);

    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setRedirect')->with('<front>');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testReloadForm(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'reloadForm',
      'message' => 'Test',
    ]);

    $request = Request::create('http://example.org/test?x=y&copyDataFromId=12');
    $this->requestStackMock->method('getCurrentRequest')->willReturn($request);
    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setResponse')->with(
      new FundingRedirectResponse('http://example.org/test?x=y', FundingRedirectResponse::HTTP_SEE_OTHER)
    );
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testReloadFormWithCopyFromId(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'reloadForm',
      'message' => 'Test',
      'copyDataFromId' => 23,
    ]);

    $request = Request::create('http://example.org/test?x=y&copyDataFromId=12');
    $this->requestStackMock->method('getCurrentRequest')->willReturn($request);
    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setResponse')->with(
      new FundingRedirectResponse(
        'http://example.org/test?x=y&copyDataFromId=23',
        FundingRedirectResponse::HTTP_SEE_OTHER
      )
    );
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testLoadEntity(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'loadEntity',
      'message' => 'Test',
      'entityType' => 'FundingCase',
      'entityId' => 12,
    ]);

    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setRedirect')->with(
      'civiremote_funding.case',
      ['fundingCaseId' => $submitResponse->getEntityId()],
    );
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testLoadEntityUnknown(): void {
    $submitResponse = new FormSubmitResponse([
      'action' => 'loadEntity',
      'message' => 'Test',
      'entityType' => 'UnknownEntity',
      'entityId' => 12,
    ]);

    $this->messengerMock->expects(static::once())->method('addMessage')->with('Test');
    $this->formStateMock->expects(static::once())->method('setRedirect')->with('<front>');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

  public function testUnknownAction(): void {
    $submitResponse = new FormSubmitResponse(['action' => 'someAction']);

    $this->messengerMock->expects(static::never())->method('addMessage');
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Unknown response action "someAction"');
    $this->handler->handleSubmitResponse($submitResponse, $this->formStateMock);
  }

}
