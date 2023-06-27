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

use Assert\Assertion;
use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\civiremote_funding\Api\DTO\Option;
use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("civiremote_funding_application_history_status_change")
 */
final class CiviremoteFundingApplicationHistoryStatusChange extends RenderElement {

  /**
   * @inheritDoc
   */
  public function getInfo(): array {
    return [
      // Instance of ApplicationProcessActivity.
      '#activity' => NULL,
      // Array mapping status to \Drupal\civiremote_funding\Api\DTO\Option.
      // Instance of \Drupal\civiremote_funding\Api\DTO\Option.
      '#status_option' => NULL,
      '#title' => 'Status: @status',
      '#created_date_title' => $this->t('Date'),
      '#source_contact_title' => $this->t('Performed by'),
      '#pre_render' => [
        [__CLASS__, 'preRenderActivity'],
      ],
    ];
  }

  /**
   * @phpstan-param array<string, mixed> $element
   *
   * @phpstan-return array<string, mixed>
   */
  public static function preRenderActivity(array $element): array {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = \Drupal::service('date.formatter');

    Assertion::string($element['#title']);
    Assertion::isInstanceOf($element['#activity'], ApplicationProcessActivity::class);
    $activity = $element['#activity'];
    Assertion::isInstanceOf($element['#status_option'], Option::class);
    /** @var \Drupal\civiremote_funding\Api\DTO\Option $statusOption */
    $statusOption = $element['#status_option'];

    $element['activity'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => ['data-activity-kind' => 'workflow'],
      // @phpstan-ignore-next-line
      '#title' => [
        '#theme' => 'civiremote_funding_application_history_title',
        '#title' => \Drupal::translation()->translate($element['#title'], [
          '@status' => $statusOption->getLabel(),
        ]),
        '#icon' => $statusOption->getIcon(),
        '#icon_color' => $statusOption->getColor(),
      ],
      'created_date' => [
        '#type' => 'item',
        '#title' => $element['#created_date_title'],
        '#markup' => $dateFormatter->format($activity->getCreatedDate()->getTimestamp()),
      ],
      'source_contact' => [
        '#type' => 'item',
        '#title' => $element['#source_contact_title'],
        '#markup' => htmlentities($activity->getSourceContactName()),
      ],
    ];

    return $element;
  }

}
