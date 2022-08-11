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

namespace Drupal\civiremote_funding\Api\Form;

use Drupal\json_forms\Form\Util\JsonConverter;

final class FundingForm {

  private \stdClass $jsonSchema;

  private \stdClass $uiSchema;

  /**
   * @var array<string, mixed>
   */
  private array $data;

  /**
   * @param array{jsonSchema: array<string, mixed>, uiSchema: array<string, mixed>, data?: array<string, mixed>} $value
   *
   * @return self
   */
  public static function fromApiResultValue(array $value): self {
    $jsonSchema = JsonConverter::toStdClass($value['jsonSchema']);
    $uiSchema = JsonConverter::toStdClass($value['uiSchema']);

    return new self($jsonSchema, $uiSchema, $value['data'] ?? []);
  }

  /**
   * @param \stdClass $jsonSchema
   * @param \stdClass $uiSchema
   * @param array<string, mixed> $data
   */
  public function __construct(\stdClass $jsonSchema, \stdClass $uiSchema, array $data) {
    $this->jsonSchema = $jsonSchema;
    $this->uiSchema = $uiSchema;
    $this->data = $data;
  }

  public function getJsonSchema(): \stdClass {
    return $this->jsonSchema;
  }

  public function getUiSchema(): \stdClass {
    return $this->uiSchema;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

}
