<?php declare(strict_types=1);
namespace T3\T3oodle\Traits\Model;

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
