<?php

namespace App\Application\Account\Command;

final class CloseAccount
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
