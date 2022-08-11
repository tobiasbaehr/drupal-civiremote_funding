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

namespace Drupal\civiremote_funding\Api;

use Assert\Assertion;
use CMRF\Core\Core;
use Drupal\Core\Config\ImmutableConfig;

final class CiviCRMApiClient implements CiviCRMApiClientInterface {

  private Core $cmrfCore;

  private string $connectorId;

  public static function create(
    Core $cmrfCore,
    ImmutableConfig $config,
    string $connectorConfigKey
  ): self {
    $connectorId = $config->get($connectorConfigKey);
    Assertion::string($connectorId);

    return new static($cmrfCore, $connectorId);
  }

  public function __construct(Core $cmrfCore, string $connectorId) {
    $this->cmrfCore = $cmrfCore;
    $this->connectorId = $connectorId;
  }

  public function executeV3(string $entity, string $action, array $parameters = [], array $options = []): array {
    $call = $this->cmrfCore->createCallV3($this->connectorId, $entity, $action, $parameters, $options);

    // @todo Throw exception on error
    return $this->cmrfCore->executeCall($call) ?? [];
  }

  public function executeV4(string $entity, string $action, array $parameters = []): array {
    $call = $this->cmrfCore->createCallV4($this->connectorId, $entity, $action, $parameters);

    // @todo Throw exception on error
    return $this->cmrfCore->executeCall($call) ?? [];
  }

}
