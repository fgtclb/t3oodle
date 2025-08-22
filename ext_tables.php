<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    ExtensionManagementUtility::allowTableOnStandardPages('tx_t3oodle_domain_model_poll');
    ExtensionManagementUtility::allowTableOnStandardPages('tx_t3oodle_domain_model_option');
})();
