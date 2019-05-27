<?php

namespace App\Api;

use App\Entity;

class Wallet
{
    public $id;

    public $name;


    public $title;

    static public function fromEntity(Entity\Wallet $wallet): self
    {
        $self = new static();

        $self->id = $wallet->getId();
        $self->name = $wallet->getName();

        $self->title = (string) $wallet;

        return $self;
    }
}
