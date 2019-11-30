<?php

namespace App\Infrastructure\Aggregator;

use App\Infrastructure\Aggregator\Metadata\InvalidValueException;
use App\Infrastructure\Aggregator\Metadata\Value;
use function forward_static_call;

final class Metadata
{
    /**
     * @var Value
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
     * @param $value
     *
     * @return Metadata
     */
    static public function withMetadata(?Metadata $parent, string $key, Value $value): Metadata
    {
        $self = new Metadata();

        $self->parent = $parent;
        $self->key = $key;
        $self->value = $value;

        return $self;
    }

    public function getValue(string $key): ?Value
    {
        if ($this->key === $key) {
            return $this->value;
        }

        return ($this->parent) ? $this->parent->getValue($key) : null;
    }

    public function toArray(): array
    {
        return [
            'parent' => $this->parent->toArray(),
            'key' => $this->key,
            'value' => $this->value->toArray(),
        ];
    }

    /**
     * @param array $metadata
     *
     * @return static
     *
     * @throws InvalidValueException
     */
    static public function fromArray(array $metadata): self
    {
        $self = new Metadata();

        $self->parent = (!empty($metadata['parent'])) ? Metadata::fromArray($metadata['parent']) : null;
        $self->key = $metadata['key'];

        $valueClass = $metadata['value']['class'];
        $self->value = forward_static_call([$valueClass, 'deserialize'], $metadata['value']['context']);

        return $self;
    }
}
