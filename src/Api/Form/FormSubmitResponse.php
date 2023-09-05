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

/**
 * @phpstan-type submitResponseT array{
 *   action: string,
 *   message?: string,
 *   errors?: array<string, non-empty-array<string>>,
 *   files?: array<string, string>,
 *   entityType?: string,
 *   entityId?: int,
 * }
 */
final class FormSubmitResponse {

  private string $action;

  /**
   * @var array<string, non-empty-array<string>>
   */
  private array $errors;

  private ?string $message;

  /**
   * @var array<string, string>
   */
  private array $files;

  private ?string $entityType;

  private ?int $entityId;

  /**
   * @phpstan-param submitResponseT $value
   *
   * @return self
   */
  public static function fromApiResultValue(array $value): self {
    return new self(
      $value['action'],
      $value['message'] ?? NULL,
      $value['errors'] ?? [],
      $value['files'] ?? [],
      $value['entity_type'] ?? NULL,
      $value['entity_id'] ?? NULL,
    );
  }

  /**
   * @param array<string, non-empty-array<string>> $errors
   * @param array<string, string> $files
   *   Submitted file URIs mapped to CiviCRM file URIs.
   */
  public function __construct(
    string $action,
    ?string $message,
    array $errors,
    array $files,
    ?string $entityType,
    ?int $entityId
  ) {
    $this->action = $action;
    $this->errors = $errors;
    $this->message = $message;
    $this->files = $files;
    $this->entityType = $entityType;
    $this->entityId = $entityId;
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

}
