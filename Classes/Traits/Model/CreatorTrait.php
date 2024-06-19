<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Traits\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\FrontendUser;

trait CreatorTrait
{
    use DynamicUserProperties;

    /**
     * @var FrontendUser
     */
    protected ?FrontendUser $creator;

    /**
     * @var string
     */
    protected $creatorName = '';

    /**
     * @var string
     */
    protected $creatorMail = '';

    /**
     * @var string
     */
    protected $creatorIdent = '';

    public function getCreator(): ?FrontendUser
    {
        return $this->creator;
    }

    public function setCreator(FrontendUser $creator): void
    {
        $this->creator = $creator;
    }

    public function getCreatorName(): string
    {
        if ($this->getCreator()) {
            return $this->getPropertyDynamically($this->getCreator(), 'name');
        }

        return $this->creatorName;
    }

    public function setCreatorName(string $creatorName): void
    {
        $this->creatorName = $creatorName;
    }

    public function getCreatorMail(): string
    {
        if ($this->getCreator()) {
            return $this->getPropertyDynamically($this->getCreator(), 'mail', false);
        }

        return $this->creatorMail;
    }

    public function setCreatorMail(string $creatorMail): void
    {
        $this->creatorMail = $creatorMail;
    }

    public function getCreatorIdent(): string
    {
        return $this->creatorIdent;
    }

    public function setCreatorIdent(string $creatorIdent): void
    {
        $this->creatorIdent = $creatorIdent;
    }
}
