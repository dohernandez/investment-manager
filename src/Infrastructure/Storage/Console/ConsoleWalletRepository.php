<?php

namespace App\Infrastructure\Storage\Console;

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

        /** @var Wallet $wallet */
        return [
            'id' => $wallet->getId(),
        ];
    }
}
