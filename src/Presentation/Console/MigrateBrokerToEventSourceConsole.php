<?php

namespace App\Presentation\Console;

use App\Application\Broker\Command\RegisterBroker;
use App\Repository\BrokerRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateBrokerToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-broker-event-source';

    /**
     * @var BrokerRepository
     */
    private $brokerRepository;

    public function __construct(
        BrokerRepository $brokerRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->brokerRepository = $brokerRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate brokers data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $brokers = $this->brokerRepository->findAll();

        $count = 0;
        $io->progressStart(count($brokers));

        foreach ($brokers as $broker) {
            $this->bus->dispatch(
                new RegisterBroker(
                    $broker->getName(),
                    $broker->getSite(),
                    $this->convertCurrencyEventSource($broker->getCurrency())
                )
            );

            $count++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count . ' brokers were migrated successfully.');
    }
}
