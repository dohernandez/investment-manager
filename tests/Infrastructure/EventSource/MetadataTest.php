<?php

namespace App\Tests\Infrastructure\EventSource;

use App\Infrastructure\EventSource\Metadata;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group infrastructure
 * @group aggregator
 */
final class MetadataTest extends TestCase
{
    /**
     * @dataProvider getValueDataProvider
     *
     * @param Metadata $metadata
     * @param string $key
     * @param mixed $value
     */
    public function testGetValue(Metadata $metadata, string $key, $value)
    {
        $this->assertEquals($value, $metadata->getValue($key));
    }

    public function getValueDataProvider()
    {
        $value = 'VALUE 1';
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
                    'VALUE 2'
                ),
                $key,
                null,
            ],

            'get value from parent key exists' => [
                Metadata::withMetadata(
                    $metadata,
                    'key2',
                    'VALUE 2'
                ),
                $key,
                $value,
            ],
        ];
    }
}
