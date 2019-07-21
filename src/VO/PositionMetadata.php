<?php

namespace App\VO;

class PositionMetadata
{
    const FIELD_DIVIDEND_PROJECTED = 'dividend_projected';

    /**
     * @var Money
     */
    private $dividendProjected;

    /**
     * @return Money
     */
    public function getDividendProjected(): ?Money
    {
        return $this->dividendProjected;
    }

    public function setDividendProjected(?Money $dividendProjected): self
    {
        $self = clone $this;

        $self->dividendProjected = $dividendProjected;

        return $self;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_DIVIDEND_PROJECTED => $this->getDividendProjected() ? $this->getDividendProjected()->toArray() : null,
        ];
    }

    public static function fromArray(?array $metadata): self
    {
        $self = new static();

        $self->dividendProjected = $metadata[self::FIELD_DIVIDEND_PROJECTED] ? Money::fromArray($metadata[self::FIELD_DIVIDEND_PROJECTED]) : null;

        return $self;
    }
}
