<?php

namespace App\Infrastructure\EventSource;

use DateTime;

final class Metadata
{
    /**
     * @var DateTime|null
     */
    private $updatedAt = null;

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     *
     * @return self
     */
    public function changeUpdatedAt(?DateTime $updatedAt): self
    {
        $self = clone $this;
        $self->updatedAt = $updatedAt;

        return $self;
}
}
