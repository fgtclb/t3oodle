<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Extbase\TypeConverter;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

class BasePollObjectConverter extends PersistentObjectConverter
{
    protected $targetType = BasePoll::class;

    protected $priority = 2;

    public function getTargetTypeForSource($source, string $originalTargetType, PropertyMappingConfigurationInterface $configuration = null): string
    {
        $targetType = parent::getTargetTypeForSource($source, $originalTargetType, $configuration);
        if (BasePoll::class === $targetType && is_array($source) && isset($source['type'])) {
            return $source['type'];
        }

        return $targetType;
    }

    protected function buildObject(array &$possibleConstructorArgumentValues, string $objectType): object
    {
        $type = $possibleConstructorArgumentValues['type'] ?? null;
        if ($type) {
            return parent::buildObject($possibleConstructorArgumentValues, $type);
        }

        return parent::buildObject($possibleConstructorArgumentValues, $objectType);
    }
}
