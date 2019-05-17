<?php

namespace App\Api;

use App\Entity;

class StockDividend
{
    public $id;

    public $exDate;

    public $paymentDate;

    public $recordDate;

    public $status;

    public $value;

    public $title;

    static public function fromEntity(Entity\StockDividend $stockDividend): self
    {
        $self = new static();

        $self->id = $stockDividend->getId();
        $self->exDate = $stockDividend->getExDate();
        $self->paymentDate = $stockDividend->getPaymentDate();
        $self->recordDate = $stockDividend->getRecordDate();
        $self->status = $stockDividend->getStatus();
        $self->value = $stockDividend->getValue();

        $self->title = (string) $stockDividend;

        return $self;
    }
}
