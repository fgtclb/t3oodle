<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Tests\Unit\Utility;

use FGTCLB\T3oodle\Utility\DateTimeUtility;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class DateTimeUtilityTest extends UnitTestCase
{
    #[Test]
    public function nowReturnsDateTimeObject(): void
    {
        $this->assertInstanceOf(\DateTime::class, DateTimeUtility::now());
    }

    #[Test]
    public function midnightReturnsMidnight(): void
    {
        $midnight = new \DateTime('today midnight');
        $this->assertSame($midnight->getTimestamp(), DateTimeUtility::today()->getTimestamp());
    }
}
