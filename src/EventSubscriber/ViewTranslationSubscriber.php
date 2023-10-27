<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Drupal\civiremote_funding\EventSubscriber;

use Drupal\civiremote_funding\ViewTranslator;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Translate view when view is added/changed.
 */
final class ViewTranslationSubscriber implements EventSubscriberInterface {

  private ViewTranslator $viewTranslator;

  public function __construct(ViewTranslator $viewTranslator) {
    $this->viewTranslator = $viewTranslator;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
    ];
  }

  public function onConfigSave(ConfigCrudEvent $event): void {
    if (str_starts_with($event->getConfig()->getName(), 'views.view.civiremote_funding_')) {
      $this->viewTranslator->translateView($event->getConfig()->getName());
    }
  }

}
