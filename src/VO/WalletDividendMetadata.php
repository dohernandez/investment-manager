<?php

namespace App\VO;

abstract class WalletDividendMetadata
{
    const FIELD_PROJECTED = 'projected';
    const FIELD_PAID = 'paid';

    /** @var Money */
    protected $projected;

    /** @var Money */
    protected $paid;

    public function getProjected(): ?Money
    {
        return $this->projected;
    }

    abstract public function setProjected(?Money $projected): self;

    public function getPaid(): ?Money
    {
        return $this->paid;
    }

    public function setPaid(?Money $paid): self
    {
        $self = clone $this;

        $self->paid = $paid;

        return $self;
    }

    public function increasePaid(Money $money): self
    {
        $paid = $this->getPaid();

        if ($paid === null) {
            $paid = Money::fromCurrency($money->getCurrency());
        }

        return $this->setPaid($paid->increase($money));
    }
}
