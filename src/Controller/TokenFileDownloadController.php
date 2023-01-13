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

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TokenFileDownloadController extends ControllerBase {

  private FundingFileManager $fundingFileManager;

  public function __construct(FundingFileManager $fundingFileManager) {
    $this->fundingFileManager = $fundingFileManager;
  }

  public function download(string $token, string $filename): Response {
    $fundingFile = $this->fundingFileManager->loadByTokenAndFilename($token, $filename);
    if (NULL === $fundingFile) {
      return new Response(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $fundingFile->getFile();
    if (!is_file($file->getFileUri())) {
      throw new NotFoundHttpException();
    }

    return new BinaryFileResponse(
      $file->getFileUri(),
      Response::HTTP_OK,
      ['Content-Type' => $file->getMimeType()],
      FALSE,
    );
  }

}
