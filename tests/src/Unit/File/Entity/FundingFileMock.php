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

namespace Drupal\Tests\civiremote_funding\Unit\File\Entity;

use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\file\FileInterface;

final class FundingFileMock extends AbstractEntityMock implements FundingFileInterface {

  private FileInterface $file;

  private ?string $id = NULL;

  private ?string $token = NULL;

  private ?string $civiUri = NULL;

  private string $lastModified = 'Thu, 01 Jan 1970 00:00:00 GMT';

  private int $lastAccess = 0;

  public function __construct(FileInterface $file) {
    $this->file = $file;
  }

  public function getFile(): FileInterface {
    return $this->file;
  }

  public function getFileId(): ?string {
    return NULL === $this->file->id() ? NULL : (string) $this->file->id();
  }

  public function setFile(FileInterface $file): FundingFileInterface {
    $this->file = $file;

    return $this;
  }

  public function getToken(): ?string {
    return $this->token;
  }

  public function setToken(string $token): FundingFileInterface {
    $this->token = $token;

    return $this;
  }

  public function getCiviUri(): ?string {
    return $this->civiUri;
  }

  public function setCiviUri(string $civiUri): FundingFileInterface {
    $this->civiUri = $civiUri;

    return $this;
  }

  public function getLastModified(): string {
    return $this->lastModified;
  }

  public function setLastModified(string $lastModified): FundingFileInterface {
    $this->lastModified = $lastModified;

    return $this;
  }

  public function getLastAccess(): int {
    return $this->lastAccess;
  }

  public function setLastAccess(int $lastAccess): FundingFileInterface {
    $this->lastAccess = $lastAccess;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function uuid(): ?string {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   *
   * @return string|null
   *   Actually the ID is an integer, though Drupal returns a string. So we do
   *   the same.
   */
  public function id() {
    return $this->id;
  }

  /**
   * @param string|null $id
   *   Actually the ID is an integer, though Drupal returns a string. So we do
   *   the same.
   */
  public function setId(?string $id): self {
    $this->id = $id;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeId(): string {
    return 'civicrm_funding_file';
  }

  /**
   * {@inheritDoc}
   */
  public function label() {
    return 'CiviRemote Funding file entity';
  }

}
