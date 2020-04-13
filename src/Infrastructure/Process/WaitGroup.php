<?php

namespace App\Infrastructure\Process;

use App\Infrastructure\Context\Context;
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
     * @param int|null $wait Allow to wait only for part of the group. Default wait for all.
     *
     * @return int Process still running
     */
    public function wait(?int $wait = null): int
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
