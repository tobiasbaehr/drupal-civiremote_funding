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

use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\civiremote_funding\File\FundingFileRouter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Element\ManagedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FileUploadCallback implements ContainerInjectionInterface {

  public const FILES_PROPERTY_KEY = 'files';

  private FundingFileManager $fundingFileManager;

  private FundingFileRouter $fundingFileRouter;

  public function __construct(
    FundingFileManager $fundingFileManager,
    FundingFileRouter $fundingFileRouter
  ) {
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
      $container->get(FundingFileManager::class),
      $container->get(FundingFileRouter::class),
    );
  }

  /**
   * @param array<string, mixed> $element
   * @param array<string, mixed> $completeForm
   */
  public static function validate(array &$element, FormStateInterface $formState, array &$completeForm): void {
    static::create(\Drupal::getContainer())->doValidate($element, $formState, $completeForm);
  }

  /**
   * @param array<string, mixed> $element
   * @param array<string, mixed> $completeForm
   */
  public function doValidate(array &$element, FormStateInterface $formState, array &$completeForm): void {
    ManagedFile::validateManagedFile($element, $formState, $completeForm);

    $files = $element['#files'] ?? [];
    // #multiple is not used, so we can use just the first file.
    $file = reset($files);
    if (FALSE === $file) {
      // No file set.
      $formState->unsetValue($element['#parents']);

      return;
    }

    /** @var \Drupal\file\FileInterface $file */
    if ($file->isPermanent()) {
      // File unchanged.
      $formState->setValueForElement($element, $element['#_original_value']);

      return;
    }

    /** @phpstan-var array<string, array{uri: string, fundingFile: \Drupal\civiremote_funding\Entity\FundingFileInterface}> $filesProperty */
    $filesProperty = $formState->get(self::FILES_PROPERTY_KEY) ?? [];

    // @phpstan-ignore-next-line
    $clickedButton = end($formState->getTriggeringElement()['#parents']);
    if ('remove_button' === $clickedButton) {
      // New file just removed.
      $formState->unsetValue($element['#parents']);
      unset($filesProperty[$file->id()]);
      $formState->set(self::FILES_PROPERTY_KEY, $filesProperty);
    }
    else {
      // New file. $fundingFile is saved on successful form submit.
      if (isset($filesProperty[$file->id()])) {
        $uri = $filesProperty[$file->id()]['uri'];
      }
      else {
        $fundingFile = $this->fundingFileManager->create($file);
        $uri = $this->fundingFileRouter->generate($fundingFile);
        $filesProperty[$file->id()] = [
          'uri' => $uri,
          'fundingFile' => $fundingFile,
        ];
        $formState->set(self::FILES_PROPERTY_KEY, $filesProperty);
      }

      $formState->setValueForElement($element, $uri);
    }
  }

}
