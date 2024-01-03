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

use Assert\Assertion;
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @codeCoverageIgnore
 */
class FundingFileRouter {

  private UrlGeneratorInterface $urlGenerator;

  public function __construct(UrlGeneratorInterface $urlGenerator) {
    $this->urlGenerator = $urlGenerator;
  }

  public function generate(FundingFileInterface $fundingFile): string {
    Assertion::notNull($fundingFile->getFile());

    return $this->urlGenerator->generate(
      'civiremote_funding.token_file_download',
      [
        'token' => $fundingFile->getToken(),
        'filename' => $fundingFile->getFile()->getFilename(),
      ],
      UrlGeneratorInterface::ABSOLUTE_URL
    );
  }

}
