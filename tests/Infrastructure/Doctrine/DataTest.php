<?php

namespace App\Tests\Infrastructure\Doctrine;

use App\Domain\Market\MarketPrice;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Doctrine\Data;
use DateTime;
use PHPUnit\Framework\TestCase;

use function array_map;
use function call_user_func_array;
use function count;
use function get_class;

class DataTest extends TestCase
{

    /**
     * @dataProvider valueDataProvider
     *
     * @param $data
     * @param $result
     */
    public function testMarshalData($data, $result)
    {
        $this->assertEquals($result, $data->marshalData());
    }

    public function valueDataProvider()
    {
        $id = '5e95ecd0a9b36';
        $priceId = 12;
        $now = '2020-04-14T19:03:12+02:00';
        $dividendYield = 6.20;
        $dividends = [4, 5];

        $data = new class(
            $id,
            new MarketPrice($priceId),
            new DateTime($now),
            $dividendYield,
            array_map(
                function ($id) {
                    return new StockDividend($id);
                },
                $dividends
            )
        ) {
            use Data;

            /**
             * @var string
             */
            private $id;

            /**
             * @var MarketPrice
             */
            private $price;

            /**
             * @var DateTime
             */
            private $updatedAt;

            /**
             * @var float|null
             */
            private $dividendYield;

            /**
             * @var array|null
             */
            private $dividends;

            public function __construct(
                string $id,
                MarketPrice $price,
                DateTime $updatedAt,
                ?float $dividendYield = null,
                ?array $dividends = null
            ) {
                $this->id = $id;
                $this->price = $price;
                $this->updatedAt = $updatedAt;
                $this->dividendYield = $dividendYield;
                $this->dividends = $dividends;
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getPrice(): MarketPrice
            {
                return $this->price;
            }

            public function getUpdatedAt(): DateTime
            {
                return $this->updatedAt;
            }

            public function getDividendYield(): ?float
            {
                return $this->dividendYield;
            }

            public function getDividends(): ?array
            {
                return $this->dividends;
            }
        };

        $result = [
            'id'            => 's:13:"' . $id . '";',
            'price'         => [
                'class' => MarketPrice::class,
                'id'    => 12,
            ],
            'updatedAt'     => [
                'class' => DateTime::class,
                'date'  => $now,
            ],
            'dividendYield' => 'd:' . $dividendYield . ';',
            'dividends'     => array_map(
                function ($id) {
                    return [
                        'class' => StockDividend::class,
                        'id'    => $id
                    ];
                },
                $dividends
            ),
        ];

        return [
            [
                $data,
                $result,
            ]
        ];
    }

    /**
     * @dataProvider valueDataProvider
     *
     * @param $result
     * @param $value
     */
    public function testUnMarshalData($result, $value)
    {
        $data = call_user_func_array([get_class($result), 'unMarshalData'], [$value]);

        $this->assertEquals($result->getId(), $data->getId());
        $this->assertEquals($result->getPrice()->getId(), $data->getPrice()->getId());
        $this->assertEquals($result->getUpdatedAt(), $data->getUpdatedAt());
        $this->assertEquals($result->getDividendYield(), $data->getDividendYield());

        $resultDividends = $result->getDividends();
        $dataDividends = $data->getDividends();

        $this->assertEquals(count($resultDividends), count($dataDividends));

        $this->assertInstanceOf(get_class($resultDividends[0]), $dataDividends[0]);
        $this->assertEquals($resultDividends[0]->getId(), $dataDividends[0]->getId());

        $this->assertInstanceOf(get_class($resultDividends[1]), $dataDividends[1]);
        $this->assertEquals($resultDividends[1]->getId(), $dataDividends[1]->getId());
    }
}
