<?php

namespace App\VO;

class WalletMetadata
{
    const FIELD_DIVIDENDS = 'dividends';

    /**
     * @var WalletDividendYearMetadata[]
     */
    private $dividends;

    /**
     * @return WalletDividendYearMetadata[]|null
     */
    public function getDividends(): ?array
    {
        return $this->dividends;
    }

    public function getDividendYear(int $year): ?WalletDividendYearMetadata
    {
        return isset($this->dividends[$year]) ? $this->dividends[$year] : null;
    }

    public function setDividendYear(int $year, ?WalletDividendYearMetadata $dividendMetadata): self
    {
        $self = clone $this;

        $self->dividends[$year] = $dividendMetadata;

        return $self;
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
            $dividends[$year] = WalletDividendYearMetadata::fromArray($dividend);
        }

        $self->dividends = $dividends;

        return $self;
    }
}
