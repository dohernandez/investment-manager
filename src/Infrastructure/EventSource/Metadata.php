<?php

namespace App\Infrastructure\EventSource;

final class Metadata
{
    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var string
     */
    private $key = '';

    /**
     * @var Metadata
     */
    private $parent = null;

    /**
     * @param Metadata $parent
     * @param string $key
     * @param mixed $value
     *
     * @return Metadata
     */
    public static function withMetadata(?Metadata $parent, string $key, $value): Metadata
    {
        $self = new Metadata();

        $self->parent = $parent;
        $self->key = $key;
        $self->value = $value;

        return $self;
    }

    public function getValue(string $key)
    {
        if ($this->key === $key) {
            return $this->value;
        }

        return ($this->parent) ? $this->parent->getValue($key) : null;
    }
}
