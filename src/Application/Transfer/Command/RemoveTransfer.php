<?php

namespace App\Application\Transfer\Command;

final class RemoveTransfer
{
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
