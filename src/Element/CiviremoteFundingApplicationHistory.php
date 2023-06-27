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
use Drupal\civiremote_funding\Api\DTO\Option;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Url;

/**
 * @RenderElement("civiremote_funding_application_history")
 */
final class CiviremoteFundingApplicationHistory extends RenderElement {

  /**
   * @inheritDoc
   */
  public function getInfo(): array {
    return [
      // Array of ApplicationProcessActivity instances.
      '#activities' => [],
      // Array mapping status to \Drupal\civiremote_funding\Api\DTO\Option.
      '#status_options' => [],
      '#unknown_status_label' => $this->t('Unknown'),
      '#theme' => 'civiremote_funding_application_history',
      '#application_title' => '',
      // Optional path of back link (without base path,
      // https://www.drupal.org/project/drupal/issues/2582295)
      '#back_link_destination' => NULL,
      '#back_link_title' => $this->t('Back'),
      '#filter_title' => $this->t('Filter'),
      '#workflow_filter_button_title' => $this->t('Hide workflow actions'),
      '#comment_filter_button_title' => $this->t('Hide comments'),
      '#pre_render' => [
        [__CLASS__, 'preRenderHistory'],
      ],
    ];
  }

  public static function preRenderHistory(array $element): array {
    $element['#status_options']['unknown'] = Option::fromArray([
      'id' => 'unknown',
      'name' => 'unknown',
      'label' => $element['#unknown_status_label'],
    ]);

    $element['#attached']['library'][] = 'civiremote_funding/application_history';

    // Allows to use https://www.drupal.org/project/fontawesome
    // @phpstan-ignore-next-line
    $element['#attached']['library'][] = 'fontawesome/fontawesome.svg.shim';
    // @phpstan-ignore-next-line
    $element['#attached']['library'][] = 'fontawesome/fontawesome.webfonts.shim';

    $element['application_title'] = [
      '#plain_text' => $element['#application_title'],
    ];
    if (NULL !== $element['#back_link_destination']) {
      $element['back_link'] = [
        '#type' => 'link',
        '#title' => $element['#back_link_title'],
        '#url' => Url::fromUserInput($element['#back_link_destination'], ['absolute' => TRUE]),
      ];
    }
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
      $element['activities'][] = self::createActivityArray($activity, $element['#status_options']);
    }

    return $element;
  }

  /**
   * @phpstan-param array<string, \Drupal\civiremote_funding\Api\DTO\Option> $statusOptions
   *
   * @phpstan-return array<string, mixed>
   */
  private static function createActivityArray(ApplicationProcessActivity $activity, array $statusOptions): array {
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
          '#status_option' => $statusOptions[$activity->getToStatus()] ?? $statusOptions['unknown'],
        ];

      case 'funding_application_create':
        return [
          '#type' => 'civiremote_funding_application_history_create',
          '#activity' => $activity,
          '#status_option' => $statusOptions['new'],
        ];

      default:
        // Ignore unknown activity types.
        return [];
    }
  }

}
