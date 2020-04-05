<?php

namespace App\Tests\Domain\Wallet;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class BookEntryTest extends TestCase
{
    /**
     * @dataProvider copyBookFromWindowDataProvider
     *
     * @param array $bookInput
     * @param DateTime $fromDate
     * @param DateTime|null $toDate
     * @param array $assert
     */
    public function testCopyBookFromWindow(array $bookInput, DateTime $fromDate, ?DateTime $toDate, array $assert)
    {
        // 30 euros
        $yearTotal = new Money(Currency::eur(), $bookInput['year_total']);

        $book = BookEntry::createBookEntry('test');
        $book->setTotal($yearTotal);

        $year2020 = (BookEntry::createYearEntry($book, $bookInput['year']))
            ->setTotal($yearTotal);
        $entries = [];
        foreach ($bookInput['year_months'] as $month => $monthTotal) {
            $entries[] = (BookEntry::createMonthEntry($year2020, $month))
                ->setTotal(new Money(Currency::eur(), $monthTotal));
        }
        $year2020->setEntries(new ArrayCollection($entries));
        $book->setEntries(new ArrayCollection([$year2020]));

        $copyBook = BookEntry::copyBookFromWindow($book, $fromDate, $toDate);

        $this->assertEquals($assert['book_total'], $copyBook->getTotal()->getValue());

        $this->assertEquals($assert['years'], $copyBook->getEntries()->count());
        foreach ($assert['year_items'] as $i => $assertYear) {
            $copyBookYear = $copyBook->getBookEntry($i);

            $this->assertEquals($assertYear['months'], $copyBookYear->getEntries()->count());
            foreach ($assertYear['month_items'] as $j => $assertMonth) {
                $this->assertEquals($assertMonth, $copyBookYear->getBookEntry($j)->getTotal()->getValue());
            }
        }
    }

    public function copyBookFromWindowDataProvider()
    {
        $book = [
            'year_total'  => 3000,
            'year'        => 2020,
            'year_months' => [
                '1' => 200,
                '2' => 500,
                '3' => 300,
                '4' => 800,
                '5' => 1200,
            ]
        ];

        return [
            'copy book from date onwards' => [
                $book,
                new DateTime('2020-04-05'),
                null,
                [
                    'book_total' => 3000,
                    'years'      => 1,
                    'year_items' => [
                        '2020' => [
                            'months'      => '2',
                            'month_items' => [
                                '4' => 800,
                                '5' => 1200,
                            ],
                        ],
                    ],
                ],
            ],
            'copy book from time windows' => [
                $book,
                new DateTime('2020-01-01'),
                new DateTime('2020-03-31'),
                [
                    'book_total' => 3000,
                    'years'      => 1,
                    'year_items' => [
                        '2020' => [
                            'months'      => '3',
                            'month_items' => [
                                '1' => 200,
                                '2' => 500,
                                '3' => 300,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
