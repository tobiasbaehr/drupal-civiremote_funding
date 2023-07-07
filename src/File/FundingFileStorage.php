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

use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\civiremote_funding\File\Exception\NoUniqueResultException;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * @method FundingFileInterface create(array $values = [])
 * @method FundingFileInterface[] loadByProperties(array $values = [])
 *
 * @codeCoverageIgnore
 */
final class FundingFileStorage extends SqlContentEntityStorage {

  /**
   * @param array<FundingFileInterface> $entities
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
   */
  public function delete(array $entities): void {
    parent::delete($entities);
  }

  // phpcs:enable

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \Drupal\civiremote_funding\File\Exception\NoUniqueResultException
   */
  public function loadOneByProperties(array $values): ?FundingFileInterface {
    $fundingFiles = $this->loadByProperties($values);

    if (count($fundingFiles) === 1) {
      return reset($fundingFiles);
    }

    if (count($fundingFiles) > 1) {
      throw new NoUniqueResultException(sprintf('Got %d results instead of one', count($fundingFiles)));
    }

    return NULL;
  }

}
