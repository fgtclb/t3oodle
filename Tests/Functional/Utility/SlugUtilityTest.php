<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Tests\Functional\Utility;

use FGTCLB\T3oodle\Utility\SlugUtility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class SlugUtilityTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'fgtclb/t3oodle',
    ];

    public static function sanitizingDataProvider(): \Generator
    {
        yield 'Simple string with no spaces' => [
            'rawText' => 'test',
            'expectedText' => 'test',
        ];
        yield 'Simple string with single space' => [
            'rawText' => 'test me',
            'expectedText' => 'test-me',
        ];
        yield 'Simple string with multi-space' => [
            'rawText' => 'test me  with multiple    spaces',
            'expectedText' => 'test-me-with-multiple-spaces',
        ];
        yield 'Complex string with single space' => [
            'rawText' => 'test me with single space but m/w slash',
            'expectedText' => 'test-me-with-single-space-but-m-w-slash',
        ];
        yield 'Complex string with multi-space' => [
            'rawText' => 'test complex   with multi-spaces and   m/w slash',
            'expectedText' => 'test-complex-with-multi-spaces-and-m-w-slash',
        ];
    }

    #[DataProvider('sanitizingDataProvider')]
    #[Test]
    public function stringIsSanitized(string $rawText, string $expectedText): void
    {
        $object = new SlugUtility('tx_t3oodle_domain_model_poll', 'slug');
        self::assertSame($expectedText, $object->sanitize($rawText));
    }
}
