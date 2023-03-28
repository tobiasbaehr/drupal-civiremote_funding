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
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\civiremote_funding\RemotePage\RemotePageClient;
use Psr\Http\Message\ResponseInterface;

class FundingFileHttpClient {
  private RemotePageClient $remotePageClient;

  public function __construct(RemotePageClient $remotePageClient) {
    $this->remotePageClient = $remotePageClient;
  }

  /**
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function get(FundingFileInterface $fundingFile): ResponseInterface {
    Assertion::notEmpty($fundingFile->getCiviUri());

    return $this->remotePageClient->request('GET', $fundingFile->getCiviUri(), [
      'headers' => [
        'If-Modified-Since' => $fundingFile->getLastModified(),
      ],
    ]);

  }

}
