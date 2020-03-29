<?php

namespace App\Domain\Market\Event;

final class StockInfoAdded
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
