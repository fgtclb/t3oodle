<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Tests\Functional\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Extbase and the DataHandler load the inline children of a poll and a vote through their foreign field, so these
 * columns need an index. Without it, every lookup reads the whole table.
 */
final class RelationIndexTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-fluid-styled-content',
    ];

    protected array $testExtensionsToLoad = [
        'fgtclb/t3oodle',
        'georgringer/numbered-pagination',
    ];

    public static function foreignFieldDataProvider(): \Generator
    {
        yield 'options of a poll' => [
            'table' => 'tx_t3oodle_domain_model_option',
            'column' => 'poll',
        ];
        yield 'votes of a poll' => [
            'table' => 'tx_t3oodle_domain_model_vote',
            'column' => 'poll',
        ];
        yield 'option values of a vote' => [
            'table' => 'tx_t3oodle_domain_model_optionvalue',
            'column' => 'vote',
        ];
    }

    #[DataProvider('foreignFieldDataProvider')]
    #[Test]
    public function foreignFieldIsIndexed(string $table, string $column): void
    {
        $indexes = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->createSchemaManager()
            ->listTableIndexes($table);

        $indexedColumns = [];
        foreach ($indexes as $index) {
            // The index names differ between the database platforms, the columns do not
            $indexedColumns[] = array_map(strtolower(...), $index->getUnquotedColumns());
        }

        self::assertContains([$column], $indexedColumns, sprintf('%s.%s has no index', $table, $column));
    }
}
