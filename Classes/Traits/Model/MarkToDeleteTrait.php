<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Traits\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
trait MarkToDeleteTrait
{
    /**
     * @var bool
     */
    protected $markToDelete = false;

    public function setMarkToDelete(bool $value = true): void
    {
        $this->markToDelete = $value;
    }

    public function isMarkToDelete(): bool
    {
        return $this->markToDelete;
    }
}
