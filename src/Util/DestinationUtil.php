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

namespace Drupal\civiremote_funding\Util;

use Symfony\Component\HttpFoundation\Request;

final class DestinationUtil {

  /**
   * Removes the base path from the destination query parameter.
   *
   * If the parameter is not prefixed with the base path it is returned
   * unchanged. This has to be used if it is used as input of
   * \Drupal\Core\Url::fromUserInput().
   *
   * https://www.drupal.org/project/drupal/issues/2582295
   *
   * @return string|null
   *   The destination query parameter without base path or NULL, if the
   *   parameter is not set.
   *
   * @see \Drupal\Core\Url::fromUserInput()
   */
  public static function getDestinationWithoutBasePath(Request $request): ?string {
    /** @var string|null $destination */
    $destination = $request->query->get('destination');
    if (NULL === $destination) {
      return NULL;
    }

    if (str_starts_with($destination, $request->getBasePath())) {
      return substr($destination, strlen($request->getBasePath()));
    }

    return $destination;
  }

}
