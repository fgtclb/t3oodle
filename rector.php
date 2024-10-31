<?php

// rector.php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Rector\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use Ssch\TYPO3Rector\Rector\General\ExtEmConfRector;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        Typo3LevelSetList::UP_TO_TYPO3_12,
    ]);

    // In order to have a better analysis from phpstan we teach it here some more things
    $rectorConfig->phpstanConfig(Typo3Option::PHPSTAN_FOR_RECTOR_PATH);

    $rectorConfig->importNames(false, false);
    $rectorConfig->importShortClasses(false);

    // Disable parallel otherwise non php file processing is not working i.e. typoscript
    $rectorConfig->disableParallel();

    // Define your target version which you want to support
    $rectorConfig->phpVersion(\Rector\ValueObject\PhpVersion::PHP_74);

    // When you use rector there are rules that require some more actions like creating UpgradeWizards for outdated TCA types.
    // To fully support you we added some warnings. So watch out for them.

    $rectorConfig->paths([
        __DIR__,
    ]);

    $rectorConfig->skip([
        __DIR__ . '/.Build/*',
        __DIR__ . '/var/*',
        __DIR__ . '.ddev/*',
    ]);

    // Modernize your TypoScript include statements for files and move from <INCLUDE /> to @import use the FileIncludeToImportStatementVisitor (introduced with TYPO3 9.0)
    // $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v9\v0\FileIncludeToImportStatementTypoScriptRector::class);
};
