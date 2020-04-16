<?php

namespace App\Infrastructure\Process;

use App\Infrastructure\Context\Context;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function sleep;

class WaitGroup
{
    /**
     * @var int
     */
    private $ttl;

    /**
     * @var ArrayCollection
     */
    private $running;

    /**
     * @var ArrayCollection
     */
    private $failed;

    /**
     * @param int $ttl Time to sleep during wait
     */
    public function __construct(int $ttl = 1)
    {
        $this->ttl = $ttl;
        $this->running = new ArrayCollection();
        $this->failed = new ArrayCollection();
    }

    public function add(Process $process)
    {
        $this->running->add($process);
    }

    /**
     * Waits for the process to terminate.
     *
     * The callback receives the process that has terminated as a first parameter.
     * The callback function allows to have feedback from the independent process after execution,
     * once the process has terminated.
     *
     * @param int|null $wait Amount of process to wait terminate for. Default wait for all processes to terminate.
     * @param Closure $callback A valid PHP callback
     *
     * @return int Process still running
     */
    public function wait(?int $wait = null, ?Closure $callback = null): int
    {
        $wait = $wait ?? $this->running->count();
//        \dump('wait for '. $wait . ' process(es)');
        while ($wait > 0) {
//            \dump('sleep for '. $this->ttl . ' second(s)');
            sleep($this->ttl);

            /** @var Process $process */
            $processes = $this->running->toArray();
            foreach ($processes as $process) {
//                \dump('process ' . $process->getPid() . ' ' . $process->getStatus());
                if ($process->isTerminated()) {
                    $this->running->removeElement($process);
//                    \dump($process->getOutput());

                    if ($error = $process->getErrorOutput()) {
                        $this->failed->add($process);
                    }

                    $wait--;

                    if ($callback) {
                        $callback($process);
                    }
                }
            }
        }

//        \dump('still running ' . $this->running->count() . ' process(es)');
        return $this->running->count();
    }

    /**
     * @return ArrayCollection|string[]
     */
    public function getFailed(): ArrayCollection
    {
        return $this->failed;
    }
}
