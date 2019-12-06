<?php

namespace App\Tests\Infrastructure\Aggregator;

final class SimpleEventMock
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
