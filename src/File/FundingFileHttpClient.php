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

namespace Drupal\civiremote_funding\File;

use Assert\Assertion;
use CMRF\Core\Core;
use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\Core\Config\ImmutableConfig;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
class FundingFileHttpClient {
  private string $apiKey;
  private ClientInterface $httpClient;
  private RemoteContactIdProviderInterface $remoteContactIdProvider;
  private string $siteKey;

  public static function create(
    Core $cmrfCore,
    ImmutableConfig $config,
    string $connectorConfigKey,
    ClientInterface $httpClient,
    RemoteContactIdProviderInterface $remoteContactIdProvider
  ): self {
    $connectorId = $config->get($connectorConfigKey);
    Assertion::string($connectorId);

    $profile = $cmrfCore->getConnectionProfile($connectorId);
    Assertion::string($profile['api_key'] ?? NULL);
    Assertion::string($profile['site_key'] ?? NULL);

    return new self($profile['api_key'], $httpClient, $remoteContactIdProvider, $profile['site_key']);
  }

  public function __construct(
    string $apiKey,
    ClientInterface $httpClient,
    RemoteContactIdProviderInterface $remoteContactIdProvider,
    string $siteKey
  ) {
    $this->apiKey = $apiKey;
    $this->httpClient = $httpClient;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
    $this->siteKey = $siteKey;
  }

  /**
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function get(FundingFileInterface $fundingFile): ResponseInterface {
    Assertion::notEmpty($fundingFile->getCiviUri());

    return $this->httpClient->request('GET', $fundingFile->getCiviUri(), [
      'timeout'  => 3.0,
      'http_errors' => FALSE,
      RequestOptions::HEADERS => [
        'If-Modified-Since' => $fundingFile->getLastModified(),
        'X-Civi-Auth' => 'Bearer ' . $this->apiKey,
        'X-Civi-Key' => $this->siteKey,
        'X-Civi-Remote-Contact-Id' => $this->remoteContactIdProvider->getRemoteContactId(),
      ],
    ]);

  }

}
