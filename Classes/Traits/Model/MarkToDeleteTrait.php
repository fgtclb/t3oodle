<?php declare(strict_types=1);
namespace T3\T3oodle\Traits\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
trait MarkToDeleteTrait
{
    /**
     * @var bool
     */
    protected $markToDelete = false;

    public function setMarkToDelete(bool $value = true)
    {
        $this->markToDelete = $value;
    }

    public function isMarkToDelete(): bool
    {
        return $this->markToDelete;
    }
}
