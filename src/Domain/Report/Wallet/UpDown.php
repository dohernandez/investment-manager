<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class UpDown implements DataInterface
{
    public const DIRECTION_UP = 'up';
    public const DIRECTION_DOWN = 'down';
    public const DIRECTION_LEFT = 'left';

    use Data;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $direction;

    public function __construct(string $value, string $direction = self::DIRECTION_UP)
    {
        $this->value = $value;
        $this->direction = $direction;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
