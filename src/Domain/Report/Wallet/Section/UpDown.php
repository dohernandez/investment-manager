<?php

namespace App\Domain\Report\Wallet\Section;

use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class UpDown implements DataInterface
{
    public const DIRECTION_UP = 'up';
    public const DIRECTION_DOWN = 'down';

    private const DBAL_KEY_DIRECTION = 'direction';
    private const DBAL_KEY_VALUE = 'value';

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

    /**
     * @inheritDoc
     */
    public function marshalData()
    {
        return [
            self::DBAL_KEY_DIRECTION => $this->direction,
            self::DBAL_KEY_VALUE     => $this->value,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        return new static($data[self::DBAL_KEY_VALUE], $data[self::DBAL_KEY_DIRECTION]);
    }
}
