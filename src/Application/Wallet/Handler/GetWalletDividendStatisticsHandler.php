<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\GetWalletDividendStatistics;
use App\Application\Wallet\Handler\Output\WalletDividendsStatistics;
use App\Application\Wallet\Handler\Output\WalletDividendStatistics;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Exception\NotFoundException;
use DateInterval;
use DateTime;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use function max;

class GetWalletDividendStatisticsHandler implements MessageHandlerInterface
{
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

        $totalYearProjected = 0;
        $dividendYearMonthsProjected = [];

        $bookDividendsProjected = $wallet->getBook()->getDividendsProjection();
        if ($bookDividendsProjected) {
            $yearBookDividendsProjected = $bookDividendsProjected->getBookEntry(Date::getYear($now));
            if ($yearBookDividendsProjected) {
                $totalYearProjected = $yearBookDividendsProjected->getTotal();

                $month = Date::getMonth($now);
                for ($i = $month; $i < $month + $message->getLimit(); $i++) {
                    $monthBookDividendsProjected = $yearBookDividendsProjected->getBookEntry($i);
                    $dividendYearMonthsProjected[$i] = $monthBookDividendsProjected ? $monthBookDividendsProjected->getTotal(
                    ) : null;
                }
            }
        }

        $monthYearPaid = null;
        $yearPaid = null;
        $totalLastYearPaid = 0;
        $dividendLastYearMonthsPaid = [];

        $bookDividendsPaid = $wallet->getBook()->getDividends();
        if ($bookDividendsPaid) {
            $lastYearBookDividendsPaid = $bookDividendsPaid->getBookEntry(
                Date::getYear((clone $now)->sub(new DateInterval('P1Y')))
            );

            if ($lastYearBookDividendsPaid) {
                $totalLastYearPaid = $lastYearBookDividendsPaid->getTotal();

                $month = Date::getMonth($now);
                for ($i = $month; $i < $month + $message->getLimit(); $i++) {
                    $monthBookDividendsPaid = $lastYearBookDividendsPaid->getBookEntry($i);
                    $dividendLastYearMonthsPaid[$i] = $monthBookDividendsPaid ? $monthBookDividendsPaid->getTotal(
                    ) : null;
                }
            }

            $yearBookDividendsPaid = $bookDividendsPaid->getBookEntry(Date::getYear($now));

            if ($lastYearBookDividendsPaid) {
                $yearPaid = $yearBookDividendsPaid->getTotal();
                if ($monthBookDividendsPaid = $yearBookDividendsPaid->getBookEntry(Date::getMonth($now))) {
                    $monthYearPaid = $monthBookDividendsPaid->getTotal();
                }
            }
        }

        $dividendYieldProjected = max(
                $totalYearProjected ? $totalYearProjected->getValue() : 0,
                1
            ) * 100 / $wallet->getBook()->getInvested()->getValue();

        return new WalletDividendStatistics(
            $dividendYieldProjected,
            $totalYearProjected,
            $dividendYearMonthsProjected,
            $monthYearPaid,
            $yearPaid,
            $totalLastYearPaid,
            $dividendLastYearMonthsPaid
        );
    }
}
