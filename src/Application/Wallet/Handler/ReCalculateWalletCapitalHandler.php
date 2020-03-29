<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\ReCalculateWalletCapital;
use App\Application\Wallet\Decorator\WalletPositionDecorateInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Infrastructure\Exception\NotFoundException;
use App\Infrastructure\Money\Money;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReCalculateWalletCapitalHandler implements MessageHandlerInterface
{
    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var WalletPositionDecorateInterface
     */
    private $walletPositionDecorate;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        WalletPositionDecorateInterface $walletPositionDecorate
    ) {
        $this->walletRepository = $walletRepository;
        $this->walletPositionDecorate = $walletPositionDecorate;
    }

    public function __invoke(ReCalculateWalletCapital $message)
    {
        $wallet = $this->walletRepository->find($message->getId());
        if ($wallet === null) {
            throw new NotFoundException(
                'Wallet not found',
                [
                    'id' => $message->getId()
                ]
            );
        }

        $this->walletPositionDecorate->decorate($wallet);

        $capital = new Money($wallet->getCurrency());
        foreach ($wallet->getPositions() as $position) {
            $capital = $capital->increase($position->getCapital());
        }

        $wallet->forceSetCapital($capital);
        $this->walletRepository->save($wallet);

        return $wallet;
    }
}
