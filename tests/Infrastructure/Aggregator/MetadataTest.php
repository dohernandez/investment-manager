<?php

namespace App\Tests\Infrastructure\Aggregator;

use App\Infrastructure\Aggregator\Metadata;
use PHPUnit\Framework\TestCase;
use function random_int;

class MetadataTest extends TestCase
{
    /**
     * @dataProvider getValueDataProvider
     */
    public function testShouldGetValue(Metadata $metadata, string $key, ?Metadata\Value $value)
    {
        $this->assertEquals($value, $metadata->getValue($key));
    }

    public function getValueDataProvider()
    {
        $value = $this->createValue();
        $key = 'key1';

        return [
            'get value key exists' => [
                $metadata = Metadata::withMetadata(
                    null,
                    $key,
                    $value
                ),
                $key,
                $value,
            ],

            'get value null, key does not exist' => [
                Metadata::withMetadata(
                    null,
                    'key2',
                    $this->createValue()
                ),
                $key,
                null,
            ],

            'get value from parent key exists' => [
                Metadata::withMetadata(
                    $metadata,
                    'key2',
                    $this->createValue()
                ),
                $key,
                $value,
            ],
        ];
    }

    private function createValue(): Metadata\Value
    {
        return new class(random_int(0, 100)) implements Metadata\Value {
            /**
             * @var int
             */
            private $num;

            public function __construct($num)
            {
                $this->num = $num;
            }

            /**
             * @inheritDoc
             */
            public function serialize(): array
            {
                return [
                    'class' => get_class($this),
                    'context' => [
                        'num' => $this->num,
                    ],
                ];
            }

            /**
             * @inheritDoc
             */
            static public function deserialize(array $value)
            {
                return new static($value['num']);
            }

            public function getNum(): int
            {
                return $this->num;
            }
        };
    }

    /**
     * @dataProvider fromArrayDataProvider
     */
    public function testShouldCreateFromArray(array $metadata, string $key, Metadata\Value $value)
    {
        $metadata = Metadata::fromArray($metadata);

        $valueFromArray = $metadata->getValue($key);

        $this->assertEquals($value->getNum(), $valueFromArray->getNum());
    }

    public function fromArrayDataProvider()
    {
        $value = $this->createValue();
        $parent = $this->createValue();
        $key = 'key1';

        return [
            'from array value' => [
                [
                    'parent' => null,
                    'key' => $key,
                    'value' => $value->serialize(),
                ],
                $key,
                $value,
            ],

            'from array value with parent' => [
                [
                    'parent' => [
                        'parent' => null,
                        'key' => $key,
                        'value' => $value->serialize(),
                    ],
                    'key' => 'key2',
                    'value' => $parent->serialize(),
                ],
                $key,
                $value,
            ],
        ];
    }
}
