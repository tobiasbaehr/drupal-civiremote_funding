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

namespace Drupal\Tests\civiremote_funding\Unit\File;

use Drupal\civiremote_funding\File\TokenGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\civiremote_funding\File\TokenGenerator
 */
final class TokenGeneratorTest extends TestCase {

  public function testGenerateToken(): void {
    $tokenGenerator = new TokenGenerator();
    $token = $tokenGenerator->generateToken();
    static::assertTrue(22 === strlen($token));
  }

}
