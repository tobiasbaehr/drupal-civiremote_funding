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

namespace Drupal\civiremote_funding\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("civiremote_funding_dashboard_group")
 */
final class CiviremoteFundingDashboardGroup extends RenderElement {

  /**
   * @{inheritDoc}
   */
  public function getInfo(): array {
    return [
      '#elements' => [],
      '#attributes' => [
        'class' => [],
      ],
      '#pre_render' => [
        [__CLASS__, 'preRenderDashboardGroup'],
      ],
    ];
  }

  public static function preRenderDashboardGroup(array $element): array {
    $element['#attached']['library'][] = 'civiremote_funding/dashboard';
    $element['#attributes']['class'][] = 'civiremote-funding-dashboard-group';

    return [
      '#type' => 'container',
      'elements' => $element['#elements'],
    ] + $element;
  }

}
