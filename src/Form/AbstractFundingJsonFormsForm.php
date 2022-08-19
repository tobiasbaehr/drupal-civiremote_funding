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

namespace Drupal\civiremote_funding\Form;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractJsonFormsForm;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Util\FormValueAccessor;
use Drupal\json_forms\Form\Validation\FormValidationMapperInterface;
use Drupal\json_forms\Form\Validation\FormValidatorInterface;
use Opis\JsonSchema\JsonPointer;

abstract class AbstractFundingJsonFormsForm extends AbstractJsonFormsForm {

  protected FormRequestHandlerInterface $formRequestHandler;

  public function __construct(FormArrayFactoryInterface $formArrayFactory,
    FormValidatorInterface $formValidator,
    FormValidationMapperInterface $formValidationMapper,
    FormRequestHandlerInterface $formRequestHandler
  ) {
    parent::__construct($formArrayFactory, $formValidator, $formValidationMapper);
    $this->formRequestHandler = $formRequestHandler;
  }

  public function getFormId(): string {
    return 'funding_form';
  }

  public function buildForm(array $form,
    FormStateInterface $form_state,
    \stdClass $jsonSchema = NULL,
    \stdClass $uiSchema = NULL,
    int $flags = 0
  ): array {
    if (!$form_state->has('jsonSchema') || !$form_state->has('uiSchema')) {
      try {
        $fundingForm = $this->formRequestHandler->getForm($this->getRequest());
      }
      catch (ApiCallFailedException $e) {
        $this->messenger()->addError($this->t('Loading form failed: @error', ['@error' => $e->getMessage()]));
        $fundingForm = new FundingForm(new \stdClass(), new \stdClass(), []);
      }
      $form_state->set('jsonSchema', $fundingForm->getJsonSchema());
      $form_state->set('uiSchema', $fundingForm->getUiSchema());
      $form_state->set('data', $fundingForm->getData());
    }

    $form = parent::buildForm(
      $form,
      $form_state,
      $form_state->get('jsonSchema'),
      $form_state->get('uiSchema'),
      static::FLAG_RECALCULATE_ONCHANGE
    );
    if (isset($fundingForm) && $this->getRequest()->isMethod('GET')) {
      FormValueAccessor::setValue($form, [], $fundingForm->getData());
    }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $formState): void {
    if (is_array($formState->get('data'))) {
      $formState->setValues($formState->getValues() + $formState->get('data'));
    }

    parent::validateForm($form, $formState);
    if (!$formState->isSubmitted() && !$formState->isValidationEnforced()) {
      return;
    }

    if ([] === $formState->getErrors()) {
      $data = $this->getSubmittedData($formState);
      try {
        $validationResponse = $this->formRequestHandler->validateForm($this->getRequest(), $data);
      }
      catch (ApiCallFailedException $e) {
        $formState->setErrorByName(
          '',
          $this->t('Error validating form: @error', ['@error' => $e->getMessage()])->render()
        );

        return;
      }

      if (!$validationResponse->isValid()) {
        $this->mapResponseErrors($validationResponse->getErrors(), $formState);
      }
    }
  }

  /**
   * @inheritDoc
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $data = $this->getSubmittedData($formState);
    try {
      $submitResponse = $this->formRequestHandler->submitForm($this->getRequest(), $data);
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError($this->t('Submitting form failed: @error', ['@error' => $e->getMessage()]));

      return;
    }

    if ('showValidation' === $submitResponse->getAction()) {
      // We cannot add errors at this stage, though this actually cannot happen
      // because we have called the remote validation in the validation step.
      $this->messenger()->addWarning($submitResponse->getMessage() ?? $this->t('Validation failed.'));
    }
    else {
      if (NULL !== $submitResponse->getMessage()) {
        $this->messenger()->addMessage($submitResponse->getMessage());
      }

      if ('closeForm' === $submitResponse->getAction()) {
        $formState->setRedirect('<front>');
      }
      elseif ('showForm' !== $submitResponse->getAction()) {
        throw new \RuntimeException(sprintf('Unknown response action "%s"', $submitResponse->getAction()));
      }
    }
  }

  /**
   * @param array<string, non-empty-array<string>> $errors
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  private function mapResponseErrors(array $errors, FormStateInterface $formState): void {
    foreach ($errors as $pointer => $messages) {
      $pointer = JsonPointer::parse($pointer);
      Assertion::notNull($pointer);
      $element = ['#parents' => $pointer->absolutePath()];
      $formState->setError($element, implode("\n", $messages));
    }
  }

}
