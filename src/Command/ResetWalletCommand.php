<?php

namespace App\Command;

use App\Entity\Operation;
use App\Entity\Position;
use App\Entity\Trade;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetWalletCommand extends Command
{
    protected static $defaultName = 'app:reset-wallet';

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, WalletRepository $walletRepository)
    {
        parent::__construct();

        $this->walletRepository = $walletRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Reset wallet.')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug to reset.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('wallet');

        $wallet = $this->walletRepository->findOneBySlug($name);

        if ($wallet === null) {
            $io->error('wallet ' . $wallet . ' not found');

            return;
        }

        $this->em->transactional(function ($em) use($wallet) {
            // Delete wallet trades
            $query = $em->createQueryBuilder()
                ->delete(Trade::class, 't')
                ->where('t.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;
            $query->execute();

            // Delete wallet operations
            $query = $em->createQueryBuilder()
                ->delete(Operation::class, 'o')
                ->where('o.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;
            $query->execute();

            // Delete wallet positions
            $query = $em->createQueryBuilder()
                ->delete(Position::class, 'p')
                ->where('p.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;
            $query->execute();

            // Set wallet to init
            $wallet->setInterest(null);
            $wallet->setConnection(null);
            $wallet->setDividend(null);
            $wallet->setCapital(null);
            $wallet->setCommissions(null);
            $wallet->setFunds($wallet->getInvested());

            $em->persist($wallet);
        });

        $io->success('reset wallet ' . $wallet . ' successfully.');
    }
}
