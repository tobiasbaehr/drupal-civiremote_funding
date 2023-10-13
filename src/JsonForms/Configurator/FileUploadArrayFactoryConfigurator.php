<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
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

namespace Drupal\civiremote_funding\JsonForms\Configurator;

use Drupal\civiremote_funding\JsonForms\FileUploadArrayFactory;
use Drupal\Core\Config\ImmutableConfig;

final class FileUploadArrayFactoryConfigurator {

  private ImmutableConfig $config;

  public function __construct(ImmutableConfig $config) {
    $this->config = $config;
  }

  public function __invoke(FileUploadArrayFactory $fileUploadArrayFactory): void {
    // @phpstan-ignore-next-line
    $fileUploadArrayFactory->setValidFileExtensions($this->config->get('file_upload_valid_extensions'));
  }

}
