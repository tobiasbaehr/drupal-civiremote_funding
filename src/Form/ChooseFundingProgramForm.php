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

use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\DTO\FundingProgram;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;

final class ChooseFundingProgramForm extends FormBase {

  protected FundingApi $fundingApi;

  protected RemoteContactIdProviderInterface $remoteContactIdProvider;

  public function __construct(FundingApi $fundingApi, RemoteContactIdProviderInterface $remoteContactIdProvider) {
    $this->fundingApi = $fundingApi;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
  }

  /**
   * @inheritDoc
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(FundingApi::class),
      $container->get(RemoteContactIdProviderInterface::class),
    );
  }

  public function getFormId(): string {
    return 'funding_choose_funding_program';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    try {
      $fundingProgramOptions = $this->getFundingProgramOptions();
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to load available funding programs: @error', ['@error' => $e->getMessage()])
      );

      return [];
    }

    return [
      'fundingProgramId' => [
        '#type' => 'select',
        '#title' => $this->t('Funding program'),
        '#options' => $fundingProgramOptions,
        '#required' => TRUE,
      ],
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
        ],
      ],
    ];
  }

  public function submitForm(array &$form, FormStateInterface $formState): void {
    /** @phpstan-var numeric-string $fundingProgramIdStr */
    $fundingProgramIdStr = $formState->getValue('fundingProgramId');
    $fundingProgramId = (int) $fundingProgramIdStr;
    try {
      $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId(
        $this->getRemoteContactId(),
        $fundingProgramId
      );
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to load funding case types: @error', ['@error' => $e->getMessage()])
      );

      return;
    }

    if (0 === count($fundingCaseTypes)) {
      $this->messenger()->addError($this->t('No funding case type available in the selected funding program.'));
    }
    else {
      // @todo Support funding programs with multiple funding case types.
      $this->redirectToApplicationForm($fundingProgramId, $fundingCaseTypes[0]->getId(), $formState);
    }
  }

  /**
   * @return array<int, string>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  private function getFundingProgramOptions(): array {
    $options = [];
    foreach ($this->fundingApi->getFundingPrograms($this->getRemoteContactId()) as $fundingProgram) {
      if ($this->isNewApplicationPossible($fundingProgram)) {
        $options[$fundingProgram->getId()] = $fundingProgram->getTitle();
      }
    }

    return $options;
  }

  private function getRemoteContactId(): string {
    return $this->remoteContactIdProvider->getRemoteContactId();
  }

  private function isInRequestPeriod(FundingProgram $fundingProgram): bool {
    $now = new \DateTime(date('Y-m-d H:i:s'));

    return $now > $fundingProgram->getRequestsStartDate() && $now < $fundingProgram->getRequestsEndDate();
  }

  private function isNewApplicationPossible(FundingProgram $fundingProgram): bool {
    return \in_array('application_create', $fundingProgram->getPermissions(), TRUE) &&
      $this->isInRequestPeriod($fundingProgram);
  }

  private function redirectToApplicationForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    FormStateInterface $formState
  ): void {
    $formState->setRedirect('civiremote_funding.new_application_form', [
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);
  }

}
