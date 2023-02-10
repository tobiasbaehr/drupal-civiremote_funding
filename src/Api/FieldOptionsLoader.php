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

namespace Drupal\civiremote_funding\Api;

final class FieldOptionsLoader {

  private CiviCRMApiClientInterface $apiClient;

  public static function new(CiviCRMApiClientInterface $apiClient): self {
    return new self($apiClient);
  }

  public function __construct(CiviCRMApiClientInterface $apiClient) {
    $this->apiClient = $apiClient;
  }

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, string>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getOptions(string $remoteContactId, string $entity, string $field, array $values = []): array {
    $result = $this->apiClient->executeV4($entity, 'getFields', [
      'remoteContactId' => $remoteContactId,
      'loadOptions' => TRUE,
      'values' => $values,
      'where' => [
        ['name', '=', $field],
      ],
      'select' => ['options'],
    ]);

    // @phpstan-ignore-next-line
    return $result['values'][0]['options'];
  }

}
