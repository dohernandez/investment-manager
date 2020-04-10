<?php

namespace App\Infrastructure\Context;

use Doctrine\Common\Collections\ArrayCollection;

use function array_merge;

final class Context
{
    private const CONTEXT_KEYS_VALUES = 'keys-values';

    /**
     * @var array
     */
    private $values;

    private function __construct(ArrayCollection $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setValue(string $key, $value): self
    {
        $values = new ArrayCollection($this->values->toArray());
        $values->set($key, $value);

        return new static($values);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->values->get($key);
    }

    public static function TODO(): self
    {
        return new static(new ArrayCollection());
    }

    /**
     * Returns context with added loosely-typed key-value pairs.
     * If key-value pairs exist in parent context already, new pairs are appended.
     *
     * @param array $keysAndValues
     *
     * @return self
     */
    public function addKeysAndValues(array $keysAndValues): self
    {
        $values = new ArrayCollection($this->values->toArray());

        $keysValues = $values->get(self::CONTEXT_KEYS_VALUES) ?? [];
        $keysAndValues = array_merge($keysValues, $keysAndValues);

        $values->set(self::CONTEXT_KEYS_VALUES, $keysAndValues);

        return new static($values);
    }

    /**
     * Returns loosely-typed key-pairs found in context.
     *
     * @return array|null
     */
    public function getKeysAndValues(): ?array
    {
        return $this->values->get(self::CONTEXT_KEYS_VALUES);
    }
}
