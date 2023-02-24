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

namespace Drupal\civiremote_funding\JsonForms\Callbacks;

use Assert\Assertion;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\civiremote_funding\File\FundingFileRouter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FileUploadCallback implements ContainerInjectionInterface {

  public const FILES_PROPERTY_KEY = 'files';

  private FileStorageInterface $fileStorage;

  private FundingFileManager $fundingFileManager;

  private FundingFileRouter $fundingFileRouter;

  public function __construct(
    FileStorageInterface $fileStorage,
    FundingFileManager $fundingFileManager,
    FundingFileRouter $fundingFileRouter
  ) {
    $this->fileStorage = $fileStorage;
    $this->fundingFileManager = $fundingFileManager;
    $this->fundingFileRouter = $fundingFileRouter;
  }

  /**
   * @inheritDoc
   *
   * @return static
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('civiremote_funding.drupal.file.storage'),
      $container->get(FundingFileManager::class),
      $container->get(FundingFileRouter::class),
    );
  }

  /**
   * Converts the value set by the managed_file form element at the given key.
   *
   * The file ID will be converted to a URI.
   *
   * @phpstan-param array<int|string> $elementKey
   */
  public static function convertValue(FormStateInterface $formState, string $callbackKey, array $elementKey): void {
    static::create(\Drupal::getContainer())->doConvertValue($formState, $callbackKey, $elementKey);
  }

  /**
   * @phpstan-param array<int|string> $elementKey
   */
  public function doConvertValue(FormStateInterface $formState, string $callbackKey, array $elementKey): void {
    /** @phpstan-var array<string|int> $fileIds */
    $fileIds = $formState->getValue($elementKey, []);
    if ([] === $fileIds) {
      // No file => remove value from JSON data.
      $formState->unsetValue($elementKey);

      return;
    }

    $fileId = (string) $fileIds[0];
    $fundingFile = $this->fundingFileManager->loadByFileId($fileId);
    if (NULL === $fundingFile) {
      // New file. $fundingFile is saved on successful form submit.
      $file = $this->fileStorage->load($fileId);
      Assertion::notNull($file);
      /** @var \Drupal\file\FileInterface $file */
      $fundingFile = $this->fundingFileManager->create($file);
      $uri = $this->fundingFileRouter->generate($fundingFile);
    }
    else {
      // File unchanged.
      $uri = $fundingFile->getCiviUri();
    }

    $formState->setValue($elementKey, $uri);
    $formState->set([self::FILES_PROPERTY_KEY, $fileId], [
      'uri' => $uri,
      'fundingFile' => $fundingFile,
    ]);
  }

}
