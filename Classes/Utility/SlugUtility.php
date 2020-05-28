<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlugUtility
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var SlugHelper
     */
    private $slugHelper;

    public function __construct(string $tableName, string $fieldName)
    {
        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
        $this->slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $tableName,
            $fieldName,
            $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config']
        );
    }

    public function sanitize(string $slug): string
    {
        $slug = str_replace('/', ' ', $slug);
        $slug = preg_replace('/ {2,}/', ' ', $slug);
        return $this->slugHelper->sanitize($slug);
    }
}
