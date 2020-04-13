<?php

namespace App\Presentation\Console;

use App\Infrastructure\Money\Currency as CurrencyEventSource;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Money\Money as MoneyEventSource;
use App\Infrastructure\Process\WaitGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function array_slice;
use function call_user_func_array;
use function implode;

abstract class Console extends Command
{
    /**
     * @var MessageBusInterface
     */
    protected $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();

        $this->bus = $bus;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'process',
                null,
                InputOption::VALUE_OPTIONAL,
                'Arguments and operation to performance in a format operation:args... .
                This option is for internal command use to delegate the process to a different thread to avoid memory leak. 
                Nevertheless it can be used by advance users.
                For example: process update stock price: --process processUpdateStockPrice:5e5ee0d4a3981:IRBT:NASDAQ'
            )
            ->addOption(
                'threads',
                't',
                InputOption::VALUE_OPTIONAL,
                'Amount of threads to run at the same time',
                5
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$process = $input->getOption('process')) {
            return false;
        }

        $explode = explode(':', $process);

        $operation = $explode[0];
        $args = array_slice($explode, 1);

        call_user_func_array([$this, $operation], [$args]);

        return true;
    }

    protected function process(
        string $command,
        string $operation,
        array $args,
        WaitGroup $wg = null,
        string $env = 'dev'
    ) {
        $pwg = $wg ?? new WaitGroup();
        $args = sprintf(
            '%s:%s',
            $operation,
            implode(':', $args)
        );
        // triggering in a separate threat by running shell command line
        // to avoid memory leak due to exceeds allocated memory.
        $process = new Process(
            [
                'php',
                'bin/console',
                $command,
                '-e',
                $env,
                '--process',
                $args,
            ]
        );

        $pwg->add($process);
        $process->start();

        if (!$wg) {
            $pwg->wait();

            if ($error = $pwg->getFailed()->first()) {
                throw new ProcessFailedException($error);
            }
        }
    }

    protected function convertMoneyEventSource(?Money $money): ?MoneyEventSource
    {
        if (!$money) {
            return null;
        }

        return new MoneyEventSource(
            $this->convertCurrencyEventSource($money->getCurrency()),
            $money->getValue(),
            $money->getPrecision()
        );
    }

    protected function convertCurrencyEventSource(?Currency $currency): ?CurrencyEventSource
    {
        if (!$currency) {
            return null;
        }

        return CurrencyEventSource::fromCode($currency->getCurrencyCode());
    }

    /**
     * @param mixed $command
     *
     * @return mixed
     */
    protected function handle($command)
    {
        $envelope = $this->bus->dispatch($command);

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }

    protected function isHistorical(InputInterface $input): bool
    {
        $historical = $input->getOption('historical');

        if ($historical === false) {
            return false;
        }

        return true;
    }
}
