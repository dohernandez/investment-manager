<?php

namespace App\VO;

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

    public function getDividendYear(int $year): ?WalletDividendMetadata
    {
        return isset($this->dividends[$year]) ? $this->dividends[$year] : null;
    }

    public function setDividendYear(int $year, ?WalletDividendMetadata $dividendMetadata): self
    {
        $self = new static();

        $dividends = $this->getDividends();
        if ($dividends) {
            foreach ($dividends as $y => $dividend) {
                $self->dividends[$y] = $dividend;
            }
        }

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
            $dividends[$year] = WalletDividendMetadata::fromArray($dividend);
        }

        $self->dividends = $dividends;

        return $self;
    }
}
