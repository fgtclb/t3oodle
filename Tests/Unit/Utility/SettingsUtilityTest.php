<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Tests\Unit\Utility;

use FGTCLB\T3oodle\Utility\SettingsUtility;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SettingsUtilityTest extends UnitTestCase
{
    #[Test]
    public function classLoadable(): void
    {
        $object = new SettingsUtility();

        self::assertSame(SettingsUtility::class, get_class($object));
    }
}
