<?php

namespace App\Api;

use App\Entity;

class Account
{
    public $id;

    public $name;

    public $iban;

    public $alias;

    static public function fromEntity(Entity\Account $account): self
    {
        $self = new static();

        $self->id = $account->getId();
        $self->name = $account->getName();
        $self->iban = $account->getAccountNo();
        $self->alias = $account->getAlias();

        return $self;
    }
}
