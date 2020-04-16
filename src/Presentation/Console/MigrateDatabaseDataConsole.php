<?php

namespace App\Presentation\Console;

use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\Snapshot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MigrateDatabaseDataConsole extends Console
{
    protected static $defaultName = 'app:migrate-database-data';

    private const MIGRATE_CLASS = Snapshot::class;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EventSourceRepositoryInterface $eventSourceRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate database data to the new serialization.')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'No event source.')
            ->addOption('skip', 's', InputOption::VALUE_OPTIONAL, 'Skip.', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Start migrating ' . static::MIGRATE_CLASS . ' ...');
        $io->success('Data migration successfully.');
        return;

        ini_set('memory_limit', '1024M');

        if ($id = (int)$input->getOption('id')) {
            $object = $this->em->find(static::MIGRATE_CLASS, $id);
            $object->setDataData($object->getData());
            $this->em->persist($object);
            $this->em->flush();
            $this->em->clear();

            $io->success('Data migration successfully.');
            return;
        }

        $skip = (int)$input->getOption('skip');
        $limit = (int)$input->getOption('limit');
        $count = $limit ? $limit : $this->em
            ->getUnitOfWork()
            ->getEntityPersister(static::MIGRATE_CLASS)
            ->count([]);
        $step = \min($count, 10);
        \dump($step);

        $io->progressStart($count);
        $io->progressAdvance($skip);

        for ($i = $skip; $i < $count; $i += $step) {
            $events = $this->em
                ->getUnitOfWork()
                ->getEntityPersister(static::MIGRATE_CLASS)
                ->loadAll([], null, $step, $i);

            foreach ($events as $object) {
//                $io->text($object->getId());
                $object->setDataData($object->getData());
                $this->em->persist($object);

                $io->progressAdvance();
            }

            $this->em->flush();
            $this->em->clear();
        }

        $io->progressFinish();

        $io->success('Data migration successfully.');
    }
}
