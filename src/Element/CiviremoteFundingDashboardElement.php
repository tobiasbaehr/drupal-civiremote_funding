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
use Drupal\Core\Url;

/**
 * @RenderElement("civiremote_funding_dashboard_element")
 */
final class CiviremoteFundingDashboardElement extends RenderElement {

  /**
   * @{inheritDoc}
   */
  public function getInfo(): array {
    return [
      '#theme' => 'civiremote_funding_dashboard_element',
      '#title' => NULL,
      '#content' => NULL,
      '#url' => NULL,
      '#attributes' => [],
      '#link_attributes' => [],
      '#pre_render' => [
        [__CLASS__, 'preRenderDashboardElement'],
      ],
    ];
  }

  public static function preRenderDashboardElement(array $element): array {
    if (NULL !== $element['#url']) {
      $element['url'] = [
        '#plain_text' => $element['#url'] instanceof Url ? $element['#url']->toString() : $element['#url'],
      ];
    }

    $element['#attributes']['class'][] = 'civiremote-funding-dashboard-element';
    $element['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => $element['#attributes']['class'],
      ],
      'title' => [
        '#markup' => '<h3>' . $element['#title'] . '</h3>',
      ],
      'content' => $element['#content'],
    ];

    $element['#link_attributes']['class'][] = 'civiremote-funding-dashboard-element-link';
    $element['#attributes'] = $element['#link_attributes'];

    return $element;
  }

}
