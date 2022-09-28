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
 * @phpstan-type form array{
 *   jsonSchema: array<string, mixed>,
 *   uiSchema: array<string, mixed>,
 *   data: array<string, mixed>,
 * }
 */
final class FormSubmitResponse {

  private string $action;

  /**
   * @var array<string, non-empty-array<string>>
   */
  private array $errors;

  private ?FundingForm $form;

  private ?string $message;

  /**
   * @phpstan-param array{
   *   action: string, message?: string,
   *   errors?: array<string, non-empty-array<string>>,
   *   form?: form,
   * } $value
   *
   * @return self
   */
  public static function fromApiResultValue(array $value): self {
    if ('showForm' === $value['action']) {
      $form = FundingForm::fromApiResultValue($value);
    }
    else {
      $form = NULL;
    }

    return new self($value['action'], $value['message'] ?? NULL, $value['errors'] ?? [], $form);
  }

  /**
   * @param string $action
   * @param string|null $message
   * @param array<string, non-empty-array<string>> $errors
   * @param \Drupal\civiremote_funding\Api\Form\FundingForm|null $form
   */
  public function __construct(string $action, ?string $message, array $errors, ?FundingForm $form) {
    $this->action = $action;
    $this->errors = $errors;
    $this->message = $message;
    $this->form = $form;
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

  public function getForm(): ?FundingForm {
    return $this->form;
  }

  public function getMessage(): ?string {
    return $this->message;
  }

}
