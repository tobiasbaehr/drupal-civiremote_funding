<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Drupal\Tests\civiremote_funding\Unit;

use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\CiviCRMApiClientInterface;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Drupal\civiremote_funding\Api\FundingApi
 */
final class FundingApiTest extends UnitTestCase {

  private FundingApi $fundingApi;

  /**
   * @var \Drupal\civiremote_funding\Api\CiviCRMApiClientInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $apiClientMock;

  private string $remoteContactId = 'cid';

  protected function setUp(): void {
    parent::setUp();
    $this->apiClientMock = $this->createMock(CiviCRMApiClientInterface::class);
    $remoteContactIdProviderMock = $this->createMock(RemoteContactIdProviderInterface::class);
    $remoteContactIdProviderMock->method('getRemoteContactId')->willReturn($this->remoteContactId);

    $this->fundingApi = new FundingApi($this->apiClientMock, $remoteContactIdProviderMock);
  }

  /**
   * @covers \Drupal\civiremote_funding\Api\Form\FormValidationResponse
   */
  public function testValidateNewApplicationForm(): void {
    $this->apiClientMock->expects(static::once())->method('executeV4')
      ->with('RemoteFundingCase', 'validateNewApplicationForm', [
        'remoteContactId' => 'cid',
        'fundingProgramId' => 22,
        'fundingCaseTypeId' => 33,
        'data' => ['foo' => 'bar'],
      ])->willReturn([
        'values' => [
          'valid' => FALSE,
          'errors' => ['/foo' => ['invalid']],
        ],
      ]);

    $validationResponse = $this->fundingApi->validateNewApplicationForm(22, 33, ['foo' => 'bar']);

    static::assertFalse($validationResponse->isValid());
    static::assertSame(['/foo' => ['invalid']], $validationResponse->getErrors());
  }

  /**
   * @covers \Drupal\civiremote_funding\Api\Form\FundingForm
   */
  public function testGetNewApplicationForm(): void {
    $this->apiClientMock->expects(static::once())->method('executeV4')
      ->with('RemoteFundingCase', 'getNewApplicationForm', [
        'remoteContactId' => 'cid',
        'fundingProgramId' => 1,
        'fundingCaseTypeId' => 2,
      ])->willReturn([
        'values' => [
          'jsonSchema' => ['type' => 'string'],
          'uiSchema' => ['type' => 'Control'],
          'data' => ['foo' => 'bar'],
        ],
      ]);

    $form = $this->fundingApi->getNewApplicationForm(1, 2);

    static::assertEquals((object) ['type' => 'string'], $form->getJsonSchema());
    static::assertEquals((object) ['type' => 'Control'], $form->getUiSchema());
    static::assertSame(['foo' => 'bar'], $form->getData());
  }

  /**
   * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
   */
  public function testSubmitNewApplicationFormShowValidation(): void {
    $this->apiClientMock->expects(static::once())->method('executeV4')
      ->with('RemoteFundingCase', 'submitNewApplicationForm', [
        'remoteContactId' => 'cid',
        'fundingProgramId' => 22,
        'fundingCaseTypeId' => 33,
        'data' => ['foo' => 'bar'],
      ])->willReturn([
        'values' => [
          'action' => 'showValidation',
          'message' => 'Validation failed.',
          'errors' => ['/foo' => ['invalid']],
        ],
      ]);

    $submitResponse = $this->fundingApi->submitNewApplicationForm(22, 33, ['foo' => 'bar']);

    static::assertSame('showValidation', $submitResponse->getAction());
    static::assertSame('Validation failed.', $submitResponse->getMessage());
    static::assertSame(['/foo' => ['invalid']], $submitResponse->getErrors());
  }

  /**
   * @covers \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
   * @covers \Drupal\civiremote_funding\Api\Form\FundingForm
   */
  public function testSubmitNewApplicationFormCloseForm(): void {
    $this->apiClientMock->expects(static::once())->method('executeV4')
      ->with('RemoteFundingCase', 'submitNewApplicationForm', [
        'remoteContactId' => 'cid',
        'fundingProgramId' => 22,
        'fundingCaseTypeId' => 33,
        'data' => ['foo' => 'bar'],
      ])->willReturn([
        'values' => [
          'action' => 'closeForm',
        ],
      ]);

    $submitResponse = $this->fundingApi->submitNewApplicationForm(22, 33, ['foo' => 'bar']);

    static::assertSame('closeForm', $submitResponse->getAction());
    static::assertNull($submitResponse->getMessage());
    static::assertSame([], $submitResponse->getErrors());
  }

}
