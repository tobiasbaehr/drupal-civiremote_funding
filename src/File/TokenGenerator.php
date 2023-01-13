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

namespace Drupal\civiremote_funding\File;

final class TokenGenerator {

  private const LENGTH = 22;

  public function generateToken(): string {
    // Numbers and letters.
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;

    $str = '';
    for ($i = 0; $i < self::LENGTH; $i++) {
      $str .= chr($values[random_int(0, $max)]);
    }

    return $str;
  }

}
