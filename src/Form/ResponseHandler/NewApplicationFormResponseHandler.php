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

final class NewApplicationFormResponseHandler implements FormResponseHandlerInterface {

  private FormResponseHandlerAction $formResponseHandlerAction;

  private FormResponseHandlerFiles $formResponseHandlerFiles;

  private MessengerInterface $messenger;

  public function __construct(
    FormResponseHandlerAction $formResponseHandlerAction,
    FormResponseHandlerFiles $formResponseHandlerFiles,
    MessengerInterface $messenger
  ) {
    $this->formResponseHandlerAction = $formResponseHandlerAction;
    $this->formResponseHandlerFiles = $formResponseHandlerFiles;
    $this->messenger = $messenger;
  }

  public function handleSubmitResponse(FormSubmitResponse $submitResponse, FormStateInterface $formState): void {
    $this->formResponseHandlerFiles->handleSubmitResponse($submitResponse, $formState);

    if ('showForm' === $submitResponse->getAction()) {
      if (NULL !== $submitResponse->getMessage()) {
        $this->messenger->addMessage($submitResponse->getMessage());
      }

      $form = $submitResponse->getForm();
      Assertion::notNull($form);
      Assertion::keyExists($form->getData(), 'applicationProcessId');
      $formState->setRedirect('civiremote_funding.application_form', [
        'applicationProcessId' => $form->getData()['applicationProcessId'],
      ]);
    }
    else {
      $this->formResponseHandlerAction->handleSubmitResponse($submitResponse, $formState);
    }
  }

}
