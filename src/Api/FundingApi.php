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

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;

class FundingApi {

  private CiviCRMApiClientInterface $apiClient;

  public function __construct(CiviCRMApiClientInterface $apiClient) {
    $this->apiClient = $apiClient;
  }

  public function getNewApplicationForm(
    string $remoteContactId,
    int $fundingProgramId,
    int $fundingCaseTypeId
  ): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'getNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);

    // @todo error handling
    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @param string $remoteContactId
   * @param array<int|string, mixed> $data
   *   JSON serializable array.
   */
  public function validateNewApplicationForm(string $remoteContactId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'validateNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    // @todo error handling
    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @param string $remoteContactId
   * @param array<int|string, mixed> $data
   *   JSON serializable array.
   */
  public function submitNewApplicationForm(string $remoteContactId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'submitNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    // @todo error handling
    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

}
