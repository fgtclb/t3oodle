<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Updates;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\SchedulePoll;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * @deprecated
 * Will be removed as outdated in future release
 */
class MigrateOldPollTypes implements UpgradeWizardInterface
{
    private const TABLE_NAME = 'tx_t3oodle_domain_model_poll';
    private const DESTINATION_COLUMN_NAME = 'type';

    protected $affectedRows = 0;

    public function getIdentifier(): string
    {
        return 't3oodleMigrateOldPollTypes';
    }

    public function getTitle(): string
    {
        return 'EXT:t3oodle - Migrate old poll types';
    }

    public function getDescription(): string
    {
        $desc = 'Since t3oodle 0.9 the poll types are using Single Table Inheritance. In order to make this pattern ' .
                'to work in Extbase, the type field must contain the FQCN of the entity model to be used. ' .
                'This migration, converts "simple" and "schedule" to corresponding class names.';

        if ($this->affectedRows) {
            $desc .= ' ' . $this->affectedRows . ' row(s) affected.';
        }

        return $desc;
    }

    public function updateNecessary(): bool
    {
        $this->affectedRows = $this->getPreparedQueryBuilder()
                                   ->select('*')
                                   ->from(self::TABLE_NAME, 'poll')
                                   ->where('poll.' . self::DESTINATION_COLUMN_NAME . ' = "simple"')
                                   ->orWhere('poll.' . self::DESTINATION_COLUMN_NAME . ' = "schedule"')
                                   ->execute()
                                   ->rowCount();

        return $this->affectedRows > 0;
    }

    public function executeUpdate(): bool
    {
        $affectedRows = $this->getPreparedQueryBuilder()
                             ->update(self::TABLE_NAME, 'poll')
                             ->set('poll.' . self::DESTINATION_COLUMN_NAME, SimplePoll::class)
                             ->where('poll.' . self::DESTINATION_COLUMN_NAME . ' = "simple"')
                             ->execute();

        $affectedRows += $this->getPreparedQueryBuilder()
                              ->update(self::TABLE_NAME, 'poll')
                              ->set('poll.' . self::DESTINATION_COLUMN_NAME, SchedulePoll::class)
                              ->where('poll.' . self::DESTINATION_COLUMN_NAME . ' = "schedule"')
                              ->execute();

        return $affectedRows > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function getPreparedQueryBuilder(): QueryBuilder
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_NAME);
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder;
    }
}
