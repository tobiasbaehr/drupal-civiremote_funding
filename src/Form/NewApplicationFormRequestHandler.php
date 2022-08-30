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
use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Component\HttpFoundation\Request;

final class NewApplicationFormRequestHandler implements FormRequestHandlerInterface {

  private FundingApi $fundingApi;

  private RemoteContactIdProviderInterface $remoteContactIdProvider;

  public function __construct(FundingApi $fundingApi, RemoteContactIdProviderInterface $remoteContactIdProvider) {
    $this->fundingApi = $fundingApi;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
  }

  public function getForm(Request $request): FundingForm {
    $routeMatch = RouteMatch::createFromRequest($request);
    $fundingProgramId = $routeMatch->getParameter('fundingProgramId');
    Assertion::integerish($fundingProgramId);
    $fundingProgramId = (int) $fundingProgramId;
    $fundingCaseTypeId = $routeMatch->getParameter('fundingCaseTypeId');
    Assertion::integerish($fundingCaseTypeId);
    $fundingCaseTypeId = (int) $fundingCaseTypeId;

    return $this->fundingApi->getNewApplicationForm(
      $this->getRemoteContactId(), $fundingProgramId, $fundingCaseTypeId
    );
  }

  public function validateForm(Request $request, array $data): FormValidationResponse {
    return $this->fundingApi->validateNewApplicationForm($this->getRemoteContactId(), $data);
  }

  public function submitForm(Request $request, array $data): FormSubmitResponse {
    return $this->fundingApi->submitNewApplicationForm($this->getRemoteContactId(), $data);
  }

  private function getRemoteContactId(): string {
    return $this->remoteContactIdProvider->getRemoteContactId();
  }

}
