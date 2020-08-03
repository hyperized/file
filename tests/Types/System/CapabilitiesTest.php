<?php declare(strict_types=1);


namespace Hyperized\File\Tests\Types\System;

use Hyperized\File\Types\System\Capabilities;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension posix
 */
class CapabilitiesTest extends TestCase
{
    public function testNew(): void
    {
        self::assertTrue(
            Capabilities::new()
                ->getHasPosixExtension()
        );
    }
}