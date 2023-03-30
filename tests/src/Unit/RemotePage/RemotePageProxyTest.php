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

namespace Drupal\Tests\civiremote_funding\Unit\RemotePage;

use Drupal\civiremote_funding\RemotePage\RemotePageClient;
use Drupal\civiremote_funding\RemotePage\RemotePageProxy;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * @covers \Drupal\civiremote_funding\RemotePage\RemotePageProxy
 */
final class RemotePageProxyTest extends TestCase {

  /**
   * @var \Drupal\civiremote_funding\RemotePage\RemotePageClient&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clientMock;

  private RemotePageProxy $remotePageProxy;

  protected function setUp(): void {
    parent::setUp();
    $this->clientMock = $this->createMock(RemotePageClient::class);
    $this->remotePageProxy = new RemotePageProxy(
      $this->clientMock,
      new NullLogger(),
    );
  }

  public function testOk(): void {
    $remoteResponse = new Response(200, ['Content-Type' => ['plain/text']], 'test');
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willReturn($remoteResponse);

    $response = $this->remotePageProxy->get('http://civicrm/funding/remote/test');
    static::assertSame(200, $response->getStatusCode());
    static::assertSame('plain/text', $response->headers->get('Content-Type'));
    ob_start();
    $response->sendContent();
    $content = ob_get_contents();
    ob_end_clean();
    static::assertSame('test', $content);
  }

  public function testUnauthorized(): void {
    $remoteResponse = new Response(401);
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willReturn($remoteResponse);

    $this->expectException(ServiceUnavailableHttpException::class);
    $this->remotePageProxy->get('http://civicrm/funding/remote/test');
  }

  public function testForbidden(): void {
    $remoteResponse = new Response(403);
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willReturn($remoteResponse);

    $this->expectException(AccessDeniedHttpException::class);
    $this->remotePageProxy->get('http://civicrm/funding/remote/test');
  }

  public function testNotFound(): void {
    $remoteResponse = new Response(404);
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willReturn($remoteResponse);

    $this->expectException(NotFoundHttpException::class);
    $this->remotePageProxy->get('http://civicrm/funding/remote/test');
  }

  public function testUnexpectedStatus(): void {
    $remoteResponse = new Response(222);
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willReturn($remoteResponse);

    $this->expectException(ServiceUnavailableHttpException::class);
    $this->remotePageProxy->get('http://civicrm/funding/remote/test');
  }

  public function testGuzzleException(): void {
    $guzzleException = new TransferException('Test', 100);
    $this->clientMock->method('request')
      ->with('GET', 'http://civicrm/funding/remote/test')
      ->willThrowException($guzzleException);

    $this->expectExceptionObject(new ServiceUnavailableHttpException(NULL, '', $guzzleException, 100));
    $this->remotePageProxy->get('http://civicrm/funding/remote/test');
  }

}
