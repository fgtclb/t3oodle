<?php
namespace T3\T3oodle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class OptionValue extends AbstractEntity
{
    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var \T3\T3oodle\Domain\Model\Option
     */
    protected $option;

    /**
     * @var \T3\T3oodle\Domain\Model\Vote
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

    public function setOption(\T3\T3oodle\Domain\Model\Option $option): void
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
