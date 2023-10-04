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

use Assert\Assertion;

/**
 * @phpstan-type submitResponseT array{
 *   action: string,
 *   message?: string,
 *   errors?: array<string, non-empty-array<string>>,
 *   files?: array<string, string>,
 *   entityType?: string,
 *   entityId?: int,
 *   copyDataFromId?: int,
 * }
 */
final class FormSubmitResponse {

  /**
   * @phpstan-ignore-next-line Variable is actually initialized in constructor.
   */
  private string $action;

  /**
   * @var array<string, non-empty-array<string>>
   */
  private array $errors = [];

  private ?string $message = NULL;

  /**
   * @var array<string, string>
   */
  private array $files = [];

  private ?string $entityType = NULL;

  private ?int $entityId = NULL;

  private ?int $copyDataFromId = NULL;

  /**
   * @phpstan-param submitResponseT $value
   *
   * @return self
   */
  public static function fromApiResultValue(array $value): self {
    return new self($value);
  }

  /**
   * @phpstan-param submitResponseT $values
   */
  public function __construct(array $values) {
    Assertion::keyExists($values, 'action');
    foreach ($values as $key => $value) {
      if (property_exists($this, $key)) {
        // @phpstan-ignore-next-line
        $this->{$key} = $value;
      }
    }
  }

  public function getAction(): string {
    return $this->action;
  }

  /**
   * @return array<string, non-empty-array<string>>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  public function getMessage(): ?string {
    return $this->message;
  }

  /**
   * @return array<string, string>
   *   Submitted file URIs mapped to CiviCRM file URIs.
   */
  public function getFiles(): array {
    return $this->files;
  }

  public function getEntityType(): ?string {
    return $this->entityType;
  }

  public function getEntityId(): ?int {
    return $this->entityId;
  }

  public function getCopyDataFromId(): ?int {
    return $this->copyDataFromId;
  }

}
