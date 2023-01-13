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

namespace Drupal\civiremote_funding\Form\ResponseHandler;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class FormResponseHandlerAction implements FormResponseHandlerInterface {

  use StringTranslationTrait;
  private MessengerInterface $messenger;

  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  public function handleSubmitResponse(FormSubmitResponse $submitResponse, FormStateInterface $formState): void {
    if ('showValidation' === $submitResponse->getAction()) {
      // We cannot add errors at this stage, though this actually cannot happen
      // because we have called the remote validation in the validation step.
      $this->messenger->addWarning($submitResponse->getMessage() ?? $this->t('Validation failed.'));
    }
    else {
      if (NULL !== $submitResponse->getMessage()) {
        $this->messenger->addMessage($submitResponse->getMessage());
      }

      if ('closeForm' === $submitResponse->getAction()) {
        $formState->setRedirect('<front>');
      }
      elseif ('showForm' === $submitResponse->getAction()) {
        $form = $submitResponse->getForm();
        Assertion::notNull($form);
        $formState->set('jsonSchema', $form->getJsonSchema());
        $formState->set('uiSchema', $form->getUiSchema());
        $formState->setTemporary($form->getData());
        $formState->setRebuild();
      }
      else {
        throw new \RuntimeException(sprintf('Unknown response action "%s"', $submitResponse->getAction()));
      }
    }
  }

}
