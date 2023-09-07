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

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Controller\ControllerBase;

/**
 * This class provides the page content and the title used in breadcrumbs.
 *
 * The title shown on the page is prefixed by an additional string. For that
 * reason it's not possible to use a page in the corresponding View.
 */
final class TransferContractController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function content(int $fundingCaseId): array {
    return [
      '#title' => $this->t('Transfer Contract: @identifier', ['@identifier' => $this->title($fundingCaseId)]),
      '#type' => 'view',
      '#name' => 'civiremote_funding_transfer_contract',
      '#display_id' => 'embed_1',
      '#arguments' => [$fundingCaseId],
      '#embed' => TRUE,
    ];
  }

  public function title(int $fundingCaseId): ?string {
    $info = $this->fundingApi->getTransferContract(
      $fundingCaseId,
    );

    return NULL === $info ? NULL : $info->getIdentifier();
  }

}
