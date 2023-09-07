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
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   identifier: string,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 *   creation_contact_id: int,
 *   amount_approved: ?float,
 *   permissions: array<string>,
 *   transfer_contract_uri: ?string,
 * }
 *
 * @extends AbstractDTO<fundingCaseT>
 *
 * @codeCoverageIgnore
 */
final class FundingCase extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function getFundingProgramId(): int {
    return $this->values['funding_program_id'];
  }

  public function getFundingCaseTypeId(): int {
    return $this->values['funding_case_type_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function getRecipientContactId(): int {
    return $this->values['recipient_contact_id'];
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getModificationDate(): \DateTimeInterface {
    return new \DateTime($this->values['modification_date']);
  }

  public function getCreationContactId(): int {
    return $this->values['creation_contact_id'];
  }

  public function getAmountApproved(): ?float {
    return $this->values['amount_approved'];
  }

  /**
   * @phpstan-return array<string>
   */
  public function getPermissions(): array {
    return $this->values['permissions'];
  }

  public function getTransferContractUri(): ?string {
    return $this->values['transfer_contract_uri'];
  }

}
