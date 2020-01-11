<?php

namespace App\Domain\Transfer\Event;

final class TransferRemoved
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
