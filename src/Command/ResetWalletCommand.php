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

        if (!$wallet) {
            $io->error('wallet ' . $wallet . ' not found');
        }

        $this->em->transactional(function ($em) use($wallet) {
            $query = $em->createQueryBuilder()
                ->update(Operation::class, 'o')
                ->set('o.trade', ':trade')
                ->where('o.wallet = :wallet')
                ->setParameter('trade', null)
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;

            $query->execute();

            $query = $em->createQueryBuilder()
                ->delete(Trade::class, 't')
                ->where('t.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;

            $query->execute();

            $query = $em->createQueryBuilder()
                ->delete(Operation::class, 'o')
                ->where('o.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;

            $query->execute();

            $query = $em->createQueryBuilder()
                ->delete(Position::class, 'p')
                ->where('p.wallet = :wallet')
                ->setParameter('wallet', $wallet->getId())
                ->getQuery()
            ;

            $query->execute();

            $wallet->setInterest(0);
            $wallet->setConnection(0);
            $wallet->setDividend(0);
            $wallet->setCapital(0);
            $wallet->setCommissions(0);
            $wallet->setFunds($wallet->getInvested());

            $em->persist($wallet);
        });

        $io->success('reset wallet ' . $wallet . ' successfully.');
    }
}
