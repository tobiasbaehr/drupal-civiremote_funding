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

namespace Drupal\civiremote_funding\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * @Block(
 *   id = "civiremote_funding_dashboard_group_applications",
 *   admin_label = @Translation("CiviRemote Funding Dashboard Group Applications"),
 *   category = @Translation("CiviRemote Funding"),
 * )
 */
final class CiviremoteFundingDashboardGroupApplications extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build(): array {
    return [
      '#title' => $this->t('Funding Applications'),
      '#type' => 'civiremote_funding_dashboard_group',
      '#elements' => [
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('My Applications'),
          '#url' => Url::fromUri('base:civiremote/funding/application/list'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Manage current funding processes') . '</div>',
          ],
        ],
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('New Application'),
          '#url' => Url::fromUri('base:civiremote/funding/application/add'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Place a new application') . '</div>',
          ],
        ],
      ],
    ];
  }

}
