<?php

namespace App\Api;

use App\Entity;

class WalletDividend
{
    public $dividendProjectedYear;

    static public function fromEntity(Entity\Wallet $wallet): self
    {
        $self = new static();

        $self->dividendProjectedYear = $wallet->dividendProjectedYear();

        return $self;
    }
}
