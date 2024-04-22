<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Updates;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class MigrateOneOptionOnlySetting implements UpgradeWizardInterface
{
    private const TABLE_NAME = 'tx_t3oodle_domain_model_poll';
    private const DESTINATION_COLUMN_NAME = 'setting_max_votes_per_participant';

    protected $affectedRows = 0;

    public function getIdentifier(): string
    {
        return 't3oodleMigrateOneOptionOnlySetting';
    }

    public function getTitle(): string
    {
        return 'EXT:t3oodle - Migrate old "One option only" setting';
    }

    public function getDescription(): string
    {
        $desc = 'In t3oodle 0.6 the poll setting "oneOptionOnly" has been changed to "maxVotesPerParticipant". ' .
                'This update wizard checks "tx_t3oodle_domain_model_poll" table for old setting set and migrates it.';

        if ($this->affectedRows) {
            $desc .= ' ' . $this->affectedRows . ' row(s) affected.';
        }

        return $desc;
    }

    public function updateNecessary(): bool
    {
        $oldColumnName = $this->determineColumnName();
        if (!$oldColumnName) {
            return false;
        }
        $this->affectedRows = $this->getPreparedQueryBuilder()
                                   ->select('*')
                                   ->from(self::TABLE_NAME, 'poll')
                                   ->where('poll.' . self::DESTINATION_COLUMN_NAME . ' = 0')
                                   ->andWhere('poll.' . $oldColumnName . ' = 1')
                                   ->execute()
                                   ->rowCount();

        return $this->affectedRows > 0;
    }

    public function executeUpdate(): bool
    {
        $oldColumnName = $this->determineColumnName();
        if (!$oldColumnName) {
            return false;
        }
        $affectedRows = $this->getPreparedQueryBuilder()
                             ->update(self::TABLE_NAME, 'poll')
                             ->set('poll.' . self::DESTINATION_COLUMN_NAME, '1')
                             ->where('poll.' . self::DESTINATION_COLUMN_NAME . ' = 0')
                             ->andWhere('poll.' . $oldColumnName . ' = 1')
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

    private function determineColumnName(): ?string
    {
        $columnName = 'setting_one_option_only';
        if (!$this->doesColumnExist($columnName)) {
            $columnName = 'zzz_deleted_setting_one_option_only';
            if (!$this->doesColumnExist($columnName)) {
                return null;
            }
        }

        return $columnName;
    }

    private function doesColumnExist(string $columnName): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_NAME);
        $columns = $connection->getSchemaManager()->listTableColumns(self::TABLE_NAME);

        return array_key_exists($columnName, $columns);
    }
}
