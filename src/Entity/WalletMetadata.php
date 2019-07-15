<?php

namespace App\Entity;

class WalletMetadata
{
    const FIELD_DIVIDENDS = 'dividends';

    /**
     * @var WalletDividendMetadata[]
     */
    private $dividends;

    /**
     * @return WalletDividendMetadata[]|null
     */
    public function getDividends(): ?array
    {
        return $this->dividends;
    }

    public function setDividends(?array $dividends): self
    {
        $this->dividends = $dividends;

        return $this;
    }

    public function toArray(): array
    {
        $dividends = [];

        if ($this->getDividends() === null) {
            return null;
        }

        foreach ($this->getDividends() as $year => $dividend) {
            $dividends[$year] = $dividend->toArray();
        }

        return [
            self::FIELD_DIVIDENDS => $dividends
        ];
    }

    public static function fromArray(?array $metadata): self
    {
        $self = new static();

        if ($metadata === null) {
            return $self;
        }

        $dividends = [];

        foreach ($metadata[$self::FIELD_DIVIDENDS] as $year => $dividend) {
            $dividends[$year] = WalletDividendMetadata::fromArray($dividend);
        }

        $self->setDividends($dividends);

        return $self;
    }
}
