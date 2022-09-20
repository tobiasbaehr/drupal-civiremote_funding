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
use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\DTO\FundingProgram;
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
    return [
      'fundingProgramId' => [
        '#type' => 'select',
        '#options' => $this->getFundingProgramOptions(),
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
    Assertion::integerish($formState->getValue('fundingProgramId'));
    $fundingProgramId = (int) $formState->getValue('fundingProgramId');
    $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId(
      $this->getRemoteContactId(),
      $fundingProgramId
    );

    if (0 === count($fundingCaseTypes)) {
      $this->messenger()->addError($this->t('No funding case type available in the selected funding program'));
    }
    else {
      // @todo Support funding programs with multiple funding case types.
      $formState->setRedirect('civiremote_funding.new_application_form', [
        'fundingProgramId' => $fundingProgramId,
        'fundingCaseTypeId' => $fundingCaseTypes[0]->getId(),
      ]);
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
      if ($this->isInRequestPeriod($fundingProgram)) {
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

}
