<?php
namespace FGTCLB\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class OptionValue extends AbstractEntity
{
    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var \FGTCLB\T3oodle\Domain\Model\Option
     */
    protected $option;

    /**
     * @var \FGTCLB\T3oodle\Domain\Model\Vote
     */
    protected $vote;


    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getOption(): ?Option
    {
        return $this->option;
    }

    public function setOption(\FGTCLB\T3oodle\Domain\Model\Option $option): void
    {
        $this->option = $option;
    }

    public function getVote(): ?Vote
    {
        return $this->vote;
    }

    public function setVote(Vote $vote): void
    {
        $this->vote = $vote;
    }
}
