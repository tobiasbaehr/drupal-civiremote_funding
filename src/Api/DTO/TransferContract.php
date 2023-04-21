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

namespace Drupal\civiremote_funding\Api\DTO;

/**
 * @phpstan-type transferContractT array{
 *   funding_case_id: int,
 *   title: string,
 *   amount_approved: float,
 *   payout_process_id: int,
 *   amount_paid_out: float,
 *   amount_available: float,
 *   transfer_contract_uri: string,
 *   funding_case_type_id: int,
 *   funding_program_id: int,
 *   currency: string,
 *   funding_program_title: string,
 * }
 *
 * @extends AbstractDTO<transferContractT>
 *
 * @codeCoverageIgnore
 */
final class TransferContract extends AbstractDTO {

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function getAmountApproved(): float {
    return $this->values['amount_approved'];
  }

  public function getPayoutProcessId(): int {
    return $this->values['payout_process_id'];
  }

  public function getAmountPaidOut(): float {
    return $this->values['amount_paid_out'];
  }

  public function getAmountAvailable(): float {
    return $this->values['amount_available'];
  }

  public function getTransferContractUri(): string {
    return $this->values['transfer_contract_uri'];
  }

  public function getFundingCaseTypeId(): int {
    return $this->values['funding_case_type_id'];
  }

  public function getFundingProgramId(): int {
    return $this->values['funding_program_id'];
  }

  public function getCurrency(): string {
    return $this->values['currency'];
  }

  public function getFundingProgramTitle(): string {
    return $this->values['funding_program_title'];
  }

}
