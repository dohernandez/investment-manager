<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\GetWalletDividendStatistics;
use App\Application\Wallet\Handler\Output\WalletDividendsStatistics;
use App\Application\Wallet\Handler\Output\WalletDividendStatistics;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Exception\NotFoundException;
use App\Infrastructure\Money\Money;
use DateInterval;
use DateTime;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use function max;

class GetWalletDividendStatisticsHandler implements MessageHandlerInterface
{
    private const YEAR_MONTH_TOTAL_DIRECTION_NEXT = 'next';
    private const YEAR_MONTH_TOTAL_DIRECTION_PREVIOUS = 'previous';

    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    public function __construct(ProjectionWalletRepositoryInterface $projectionWalletRepository)
    {
        $this->projectionWalletRepository = $projectionWalletRepository;
    }

    public function __invoke(GetWalletDividendStatistics $message)
    {
        $wallet = $this->projectionWalletRepository->find($message->getId());
        if ($wallet === null) {
            throw new NotFoundException(
                'Wallet not found',
                [
                    'id' => $message->getId()
                ]
            );
        }

        $now = $message->getDate() ?? new DateTime();
        $year = Date::getYear($now);
        $month = Date::getMonth($now);

        $yearProjected = 0;
        $dividendYearMonthsProjected = [];

        $bookDividendsProjected = $wallet->getBook()->getDividendsProjected();
        if ($bookDividendsProjected) {
            $yearBookDividendsProjected = $bookDividendsProjected->getBookEntry($year);
            if ($yearBookDividendsProjected) {
                $yearProjected = $yearBookDividendsProjected->getTotal();

                $dividendYearMonthsProjected = $this->getMonthsTotal(
                    $bookDividendsProjected,
                    $year,
                    $month,
                    $message->getLimit()
                );
            }
        }

        $monthPaid = null;
        $yearPaid = null;
        $dividendYearMonthsPaid = [];
        $dividendLastYearPaid = null;
        $dividendLastYearMonthsPaid = [];

        $bookDividendsPaid = $wallet->getBook()->getDividends();
        if ($bookDividendsPaid) {
            $lastYear = Date::getYear((clone $now)->sub(new DateInterval('P1Y')));
            $lastYearBookDividendsPaid = $bookDividendsPaid->getBookEntry($lastYear);

            if ($lastYearBookDividendsPaid) {
                $dividendLastYearPaid = $lastYearBookDividendsPaid->getTotal();

                $dividendLastYearMonthsPaid = $this->getMonthsTotal(
                    $bookDividendsPaid,
                    $lastYear,
                    $month - 2, // to get previous two month
                    $message->getLimit() + 2 // to add the previous two month
                );
            }

            $yearBookDividendsPaid = $bookDividendsPaid->getBookEntry($year);
            if ($yearBookDividendsPaid) {
                $yearPaid = $yearBookDividendsPaid->getTotal();
                if ($monthBookDividendsPaid = $yearBookDividendsPaid->getBookEntry($month)) {
                    $monthPaid = $monthBookDividendsPaid->getTotal();
                }

                $dividendYearMonthsPaid = $this->getMonthsTotal(
                    $bookDividendsPaid,
                    $year,
                    $month,
                    $message->getLimit(),
                    self::YEAR_MONTH_TOTAL_DIRECTION_PREVIOUS
                );
            }
        }

        $dividendYieldProjected = max(
                $yearProjected ? $yearProjected->getValue() : 0,
                1
            ) * 100 / $wallet->getBook()->getInvested()->getValue();

        $dividendYieldPaid = 0;
        $bookDividendsPaid = $wallet->getBook()->getDividends();
        if ($bookDividendsPaid) {
            $yearBookDividendsPaid = $bookDividendsPaid->getBookEntry($year);
            $dividendYieldPaid = max(
                    $yearBookDividendsPaid->getTotal()->getValue(),
                    1
                ) * 100 / $wallet->getBook()->getInvested()->getValue();
        }

        return new WalletDividendStatistics(
            $dividendYieldProjected,
            $dividendYieldPaid,
            $yearProjected,
            $dividendYearMonthsProjected,
            $monthPaid,
            $yearPaid,
            $dividendYearMonthsPaid,
            $dividendLastYearPaid,
            $dividendLastYearMonthsPaid
        );
    }

    /**
     * @param BookEntry $rootEntry
     * @param int $year
     * @param int $month
     * @param int $limit
     * @param string $direction
     *
     * @return Money[]
     */
    private function getMonthsTotal(
        BookEntry $rootEntry,
        int $year,
        int $month,
        int $limit,
        string $direction = self::YEAR_MONTH_TOTAL_DIRECTION_NEXT
    ): array {
        $yearMonths = [];

        $yearBook = $rootEntry->getBookEntry($year);
        if (!$yearBook) {
            return $yearMonths;
        }

        while ($limit !== 0) {
            $monthBook = $yearBook->getBookEntry($month);
            $yearMonths[$month] = $monthBook ? $monthBook->getTotal() : null;

            if ($month === 12 || $month == 0) {
                $year = $direction == self::YEAR_MONTH_TOTAL_DIRECTION_NEXT ? $year + 1 : $year - 1;
                $month = $direction == self::YEAR_MONTH_TOTAL_DIRECTION_NEXT ? 0 : 12;

                $yearBook = $rootEntry->getBookEntry($year);

                if (!$yearBook) {
                    break;
                }
            }

            $month = $direction == self::YEAR_MONTH_TOTAL_DIRECTION_NEXT ? $month + 1 : $month - 1;
            $limit--;
        }

        return $yearMonths;
    }
}
