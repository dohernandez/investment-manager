<?php

namespace App\Infrastructure\Storage\Console;

use App\Domain\Wallet\Position;
use App\Domain\Wallet\Wallet;
use Doctrine\ORM\EntityManagerInterface;

use function sprintf;

final class ConsoleWalletRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array List of wallet listed. [id: ...]
     */
    public function findAll(): array
    {
        $all = $this->em
            ->createQuery(sprintf('SELECT PARTIAL w.{id} FROM %s w', Wallet::class))
            ->getResult();

        $this->em->clear();

        return array_map(
            function (Wallet $wallet) {
                return [
                    'id' => $wallet->getId(),
                ];
            },
            $all
        );
    }

    /**
     * @param string $slug
     *
     * @return array The wallet. [id: ...]
     */
    public function findBySlug(string $slug): array
    {
        if (
        !$wallet = $this->em
            ->createQuery(sprintf('SELECT PARTIAL w.{id} FROM %s w WHERE w.slug = :slug', Wallet::class))
            ->setParameter('slug', $slug)
            ->getOneOrNullResult()
        ) {
            return null;
        }

        $this->em->clear();

        /** @var Wallet $wallet */
        return [
            'id' => $wallet->getId(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findAllStocksInWalletOnOpenPositionBySlug(string $slug): array
    {
        $stocks = array_map(
            function ($stock) {
                if (is_array($stock)) {
                    $stock = reset($stock);
                }

                return [
                    'id' => $stock->getId(),
                    'symbol' => $stock->getSymbol(),
                ];
            },
            $this->em
                ->createQuery(sprintf(
                    'SELECT DISTINCT PARTIAL p.{stockId} FROM %s w INNER JOIN w.positions p WHERE w.slug = :slug AND p.status = :status',
                    Wallet::class)
                )
                ->setParameter('slug', $slug)
                ->setParameter('status', Position::STATUS_OPEN)
                ->getResult()
        );

        $this->em->clear();

        return $stocks;
    }
}
