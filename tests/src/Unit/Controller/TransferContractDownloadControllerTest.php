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

use Drupal\civiremote_funding\Api\DTO\FundingCase;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\Controller\TransferContractDownloadController;
use Drupal\civiremote_funding\RemotePage\RemotePageProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Drupal\civiremote_funding\Controller\TransferContractDownloadController
 */
final class TransferContractDownloadControllerTest extends TestCase {

  private TransferContractDownloadController $controller;

  /**
   * @var \Drupal\civiremote_funding\Api\FundingApi&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingApiMock;

  /**
   * @var \Drupal\civiremote_funding\RemotePage\RemotePageProxy&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $remotePageProxyMock;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingApiMock = $this->createMock(FundingApi::class);
    $this->remotePageProxyMock = $this->createMock(RemotePageProxy::class);
    $this->controller = new TransferContractDownloadController(
      $this->fundingApiMock,
      $this->remotePageProxyMock,
    );
  }

  public function test(): void {
    $fundingCase = $this->createFundingCase('http://example.org/transfer-contract');
    $this->fundingApiMock->method('getFundingCase')
      ->with(12)
      ->willReturn($fundingCase);
    $response = new Response();
    $this->remotePageProxyMock->expects(static::once())->method('get')
      ->with('http://example.org/transfer-contract')
      ->willReturn($response);

    static::assertSame($response, $this->controller->download(12));
  }

  public function testNoTransferContract(): void {
    $fundingCase = $this->createFundingCase(NULL);
    $this->fundingApiMock->method('getFundingCase')
      ->with(12)
      ->willReturn($fundingCase);

    static::expectException(NotFoundHttpException::class);
    $this->controller->download(12);
  }

  public function testNoFundingCase(): void {
    $this->fundingApiMock->method('getFundingCase')
      ->with(12)
      ->willReturn(NULL);

    static::expectException(NotFoundHttpException::class);
    $this->controller->download(12);
  }

  private function createFundingCase(?string $transferContractUri): FundingCase {
    return FundingCase::fromArray([
      'id' => 12,
      'identifier' => 'FC12',
      'funding_program_id' => 1,
      'funding_case_type_id' => 1,
      'status' => 'ongoing',
      'title' => 'Test',
      'recipient_contact_id' => 1,
      'creation_date' => '2023-01-02-03 04:05:06',
      'modification_date' => '2023-02-03-04 05:06:07',
      'creation_contact_id' => 2,
      'amount_approved' => 12.34,
      'permissions' => ['some_permission'],
      'transfer_contract_uri' => $transferContractUri,
    ]);
  }

}
