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

namespace Drupal\civiremote_funding\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\file\FileInterface;

interface FundingFileInterface extends EntityInterface {

  /**
   * Location for files downloaded from CiviCRM.
   */
  public const DOWNLOAD_LOCATION = 'public://civiremote_funding/download/';

  /**
   * Location for files uploaded through Drupal.
   */
  public const UPLOAD_LOCATION = 'public://civiremote_funding/upload/';

  public function getFile(): ?FileInterface;

  public function getFileId(): ?string;

  public function setFile(FileInterface $file): self;

  public function getToken(): ?string;

  public function setToken(string $token): self;

  public function getCiviUri(): ?string;

  public function setCiviUri(string $civiUri): self;

  public function getLastModified(): string;

  public function setLastModified(string $lastModified): self;

  public function getLastAccess(): int;

  public function setLastAccess(int $lastAccess): self;

}
