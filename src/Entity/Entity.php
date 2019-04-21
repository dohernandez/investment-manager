<?php

namespace App\Entity;

interface Entity
{
    /**
     * @return int|null Entity id
     */
    public function getId(): ?int;

    /**
     * @return string Entity string
     */
    public function __toString(): string;
}
