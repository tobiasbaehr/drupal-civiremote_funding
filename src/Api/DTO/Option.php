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
 * @phpstan-type optionT array{
 *   id: int|string,
 *   name: string,
 *   label: string,
 *   abbr?: ?string,
 *   description?: ?string,
 *   icon?: ?string,
 *   color?: ?string,
 * }
 *
 * @extends AbstractDTO<optionT>
 */
final class Option extends AbstractDTO {

  /**
   * @return int|string
   */
  public function getId() {
    return $this->values['id'];
  }

  public function getName(): string {
    return $this->values['name'];
  }

  public function getLabel(): string {
    return $this->values['label'];
  }

  public function getAbbr(): ?string {
    return $this->values['abbr'] ?? NULL;
  }

  public function getDescription(): ?string {
    return $this->values['description'] ?? NULL;
  }

  public function getIcon(): ?string {
    return $this->values['icon'] ?? NULL;
  }

  public function getColor(): ?string {
    return $this->values['color'] ?? NULL;
  }

}
