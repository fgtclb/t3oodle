<?php declare(strict_types = 1);
namespace T3\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
final class CookieUtility
{
    private const COOKIE_PREFIX = 'tx_t3oodle_';
    private const COOKIE_LIFETIME_DAYS = 365;

    /**
     * Get cookie value
     *
     * @param string $key
     * @return string|null
     */
    public static function get($key)
    {
        if (isset($_COOKIE[self::COOKIE_PREFIX . $key])) {
            return $_COOKIE[self::COOKIE_PREFIX . $key];
        }
        return null;
    }

    /**
     * Set cookie value
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public static function set($key, $value)
    {
        $cookieExpireDate = time() + self::COOKIE_LIFETIME_DAYS * 24 * 60 * 60;
        setcookie(
            self::COOKIE_PREFIX . $key,
            $value,
            $cookieExpireDate,
            '/',
            self::getCookieDomain(),
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieSecure'] > 0,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieHttpOnly'] == 1
        );
    }

    /**
     * Gets the domain to be used on setting cookies. The information is
     * taken from the value in $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']
     *
     * @return string The domain to be used on setting cookies
     */
    private static function getCookieDomain()
    {
        $result = '';
        $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
        if (!empty($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['cookieDomain'])) {
            $cookieDomain = $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['cookieDomain'];
        }
        if ($cookieDomain) {
            if ($cookieDomain[0] === '/') {
                $match = [];
                $matchCnt = @preg_match(
                    $cookieDomain,
                    \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
                    $match
                );
                if ($matchCnt !== false) {
                    $result = $match[0];
                }
            } else {
                $result = $cookieDomain;
            }
        }
        return $result;
    }
}
