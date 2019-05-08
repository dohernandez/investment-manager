<?php

namespace App\Api;

use App\Entity;

class Account
{
    public $id;

    public $name;

    public $accountNo;

    public $alias;

    public $title;

    static public function fromEntity(Entity\Account $account): self
    {
        $self = new static();

        $self->id = $account->getId();
        $self->name = $account->getName();
        $self->accountNo = $account->getAccountNo();
        $self->alias = $account->getAlias();

        $self->title = (string) $account;

        return $self;
    }
}
