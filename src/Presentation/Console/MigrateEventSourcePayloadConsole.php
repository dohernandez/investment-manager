<?php

namespace App\Presentation\Console;

use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MigrateEventSourcePayloadConsole extends Console
{
    protected static $defaultName = 'app:migrate-event-source-payload';

    /**
     * @var EventSourceRepositoryInterface
     */
    private $eventSourceRepository;

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

        $this->eventSourceRepository = $eventSourceRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate event source payload.')
            ->addOption('no', null, InputOption::VALUE_OPTIONAL, 'No event source.')
            ->addOption('skip', 's', InputOption::VALUE_OPTIONAL, 'Skip.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '512M');

        $io = new SymfonyStyle($input, $output);

        if ($no = (int)$input->getOption('no')) {
            $event = $this->eventSourceRepository->find($no);
            $event->setPayloadData($event->getPayload());
            $this->em->persist($event);
            $this->em->flush();
            $this->em->clear();

            $io->success('History dividend updated successfully.');
            return;
        }

        $skip = (int)$input->getOption('skip');
        $count = $this->eventSourceRepository->count([]);

        $io->progressStart($count);
        $io->progressAdvance($skip);

        for ($i = $skip; $i < $count; $i += 10) {
            $events = $this->eventSourceRepository->findBy([], null, 10, $i);

            /** @var Changed $event */
            foreach ($events as $event) {
//                $io->text($event->getNo());
                $event->setPayloadData($event->getPayload());
                $this->em->persist($event);

                $io->progressAdvance();
            }

            $this->em->flush();
            $this->em->clear();
        }

        $io->progressFinish();

        $io->success('History dividend updated successfully.');
    }
}
