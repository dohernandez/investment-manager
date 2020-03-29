<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\CalculateWalletYearDividendProjection;
use App\Application\Wallet\Decorator\WalletOpenOrOpenedPositionYearWithDividendsDecorateInterface;
use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CalculateWalletYearDividendProjectionHandler implements MessageHandlerInterface
{
    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    /**
     * @var WalletOpenOrOpenedPositionYearWithDividendsDecorateInterface
     */
    private $openedPositionYearWithDividendsDecorate;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository,
        WalletOpenOrOpenedPositionYearWithDividendsDecorateInterface $openedPositionYearWithDividendsDecorate
    ) {
        $this->walletRepository = $walletRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->openedPositionYearWithDividendsDecorate = $openedPositionYearWithDividendsDecorate;
    }

    public function __invoke(CalculateWalletYearDividendProjection $message)
    {
        $wallet = $this->walletRepository->find($message->getWalletId());

        $this->openedPositionYearWithDividendsDecorate->setYear($message->getYear());
        $this->openedPositionYearWithDividendsDecorate->decorate($wallet);

        $exchangeMoneyRates = $this->exchangeMoneyRepository->findAllByToCurrency($wallet->getCurrency());

        $wallet->calculateYearDividendProjected($message->getYear(), $exchangeMoneyRates);

        $this->walletRepository->save($wallet);

        return $wallet;
    }
}
