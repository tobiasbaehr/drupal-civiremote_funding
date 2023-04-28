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

use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("civiremote_funding_application_history")
 */
final class ApplicationHistoryRenderElement extends RenderElement {

  /**
   * @inheritDoc
   */
  public function getInfo(): array {
    return [
      // Array of ApplicationProcessActivity instances.
      '#activities' => [],
      // Array mapping status to label.
      '#status_labels' => [],
      '#theme' => 'civiremote_funding_application_history',
      '#filter_title' => $this->t('Filter'),
      '#workflow_filter_button_title' => $this->t('Hide workflow actions'),
      '#comment_filter_button_title' => $this->t('Hide comments'),
      '#pre_render' => [
        [__CLASS__, 'preRenderHistory'],
      ],
    ];
  }

  public static function preRenderHistory(array $element): array {
    $element['#attached']['library'][] = 'civiremote_funding/application_history';
    $element['filter'] = [
      '#type' => 'fieldset',
      '#title' => $element['#filter_title'],
      'workflow_filter_button' => [
        '#type' => 'button',
        '#value' => $element['#workflow_filter_button_title'],
        '#attributes' => ['data-activity-filter' => 'workflow'],
      ],
      'comment_filter_button' => [
        '#type' => 'button',
        '#value' => $element['#comment_filter_button_title'],
        '#attributes' => ['data-activity-filter' => 'comment'],
      ],
    ];

    $element['activities'] = [];
    foreach ($element['#activities'] as $activity) {
      $element['activities'][] = self::createActivityArray($activity, $element['#status_labels']);
    }

    return $element;
  }

  /**
   * @phpstan-param array<string> $statusLabels
   *
   * @phpstan-return array<string, mixed>
   */
  private static function createActivityArray(ApplicationProcessActivity $activity, array $statusLabels): array {
    switch ($activity->getActivityTypeName()) {
      case 'funding_application_comment_external':
        return [
          '#type' => 'civiremote_funding_application_history_comment',
          '#activity' => $activity,
        ];

      case 'funding_application_status_change':
        return [
          '#type' => 'civiremote_funding_application_history_status_change',
          '#activity' => $activity,
          '#status_labels' => $statusLabels,
        ];

      case 'funding_application_create':
        return [
          '#type' => 'civiremote_funding_application_history_create',
          '#activity' => $activity,
        ];

      default:
        // Ignore unknown activity types.
        return [];
    }
  }

}
