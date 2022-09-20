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

declare(strict_types = 1);

namespace Drupal\civiremote_funding\Api\DTO;

/**
 * @phpstan-type dtoT array<string, mixed>&array{
 *   id: int
 * }
 */
abstract class AbstractDTO {

  /**
   * @var array
   * @phpstan-var dtoT
   */
  protected array $values;

  /**
   * @phpstan-param dtoT $values
   */
  protected function __construct(array $values) {
    $this->values = $values;
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, $default = NULL) {
    return $this->values[$key] ?? $default;
  }

  /**
   * @return int Returns -1 for a new, unpersisted entity.
   */
  public function getId(): int {
    return $this->values['id'];
  }

  /**
   * @phpstan-return dtoT
   */
  public function toArray(): array {
    return $this->values;
  }

  protected static function toDateTimeOrNull(?string $dateTimeStr): ?\DateTime {
    return NULL === $dateTimeStr ? NULL : new \DateTime($dateTimeStr);
  }

  protected static function toDateTimeStr(\DateTimeInterface $dateTime): string {
    return $dateTime->format('Y-m-d H:i:s');
  }

  protected static function toDateTimeStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return NULL === $dateTime ? NULL : $dateTime->format('Y-m-d H:i:s');
  }

}
