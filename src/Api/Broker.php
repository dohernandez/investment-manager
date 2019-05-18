<?php

namespace App\Api;

use App\Entity;

class Broker
{
    public $id;

    public $name;

    public $site;

    public $account;

    public $title;

    static public function fromEntity(Entity\Broker $broker): self
    {
        $self = new static();

        $self->id = $broker->getId();
        $self->name = $broker->getName();
        $self->site = $broker->getSite();
        $self->account = Account::fromEntity($broker->getAccount());

        $self->title = (string) $broker;

        return $self;
    }
}
