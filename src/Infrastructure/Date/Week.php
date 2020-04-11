<?php

namespace App\Infrastructure\Date;

use DateTimeImmutable;
use DateTimeInterface;

final class Week
{
    private const VALID_WEEK_ID_REGEX = "#\d{4}-W\d{1,2}|(current)|(previous)|(upcoming)|(afternext)|(next)#";

    private const SAT = 1;
    private const SUN = 2;
    private const MON = 3;
    private const TUE = 4;
    private const WED = 5;
    private const THU = 6;
    private const FRI = 7;

    /**
     * @var string
     */
    protected $input;

    /**
     * What time is actually is at this moment
     * @var DateTimeInterface
     */
    protected $now;

    /**
     * Real date matching the week
     * @var DateTimeInterface
     */
    protected $date;

    /**
     * @param string $input yyyy-Wxx|previous|current|next
     * @param DateTimeInterface $now Current \DateTime
     */
    public function __construct($input = null, DateTimeInterface $now = null)
    {
        $this->input = $input ?: 'current';
        $this->now = $now ?: new DateTimeImmutable();
    }

    public static function fromDate(DateTimeInterface $date): self
    {
        return new self(null, clone $date);
    }

    public static function isValidId(string $weekId): bool
    {
        return (bool)preg_match(self::VALID_WEEK_ID_REGEX, $weekId);
    }

    public function previousWeek(): self
    {
        return $this->previousNthWeek(1);
    }

    public function previousNthWeek(int $n): self
    {
        return new static(null, $this->getDate()->modify("-$n weeks"));
    }

    public function nextWeek(): self
    {
        return $this->nextNthWeek(1);
    }

    public function nextNthWeek(int $n): self
    {
        return new static(null, (clone $this->getDate())->modify("+$n weeks"));
    }

    /**
     * Returns the initial date for the (ISO) week (e.g: for 2016-W49, return '2016-12-05 00:00:00', a Monday)
     *
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        if (!$this->date) {
            $this->setDateFromInput();
        }

        return clone $this->date;
    }

    public function getId(): string
    {
        return $this->getDate()->format('o-\WW');
    }

    public function getYear(): int
    {
        return (int) $this->getDate()->format('o');
    }

    public function getMonth(): int
    {
        return (int) $this->getDate()->format('m');
    }

    public function getWeek(): int
    {
        return (int) $this->getDate()->format('W');
    }

    public function getFirstDay(): DateTimeInterface
    {
        throw new \Exception('not implemented');
        $date = clone $this->getDate();
        if ($date->format('w') == '6') {
            return $date;
        }

        return $date->modify('last saturday');
    }

    public function getLastDay()
    {
        throw new \Exception('not implemented');
        $firstDay = clone $this->getFirstDay();

        return $firstDay->modify('+6 days');
    }

    /**
     * @throws \InvalidArgumentException If input is invalid
     */
    protected function setDateFromInput()
    {
        $date = $this->parseRelative($this->input);
        if (!$date) {
            $date = $this->parseIdentifier($this->input);
        }

        if (!$date) {
            throw new \InvalidArgumentException('Invalid week identifier');
        }

        $this->date = $date;
    }

    /**
     * Parse a relative representation
     *
     * @param  string $input previous|current|next
     * @return DateTimeInterface|null
     */
    protected function parseRelative($input): ?DateTimeInterface
    {
        $date = clone $this->now;

        switch ($input) {
            case 'current':
                break;

            case 'previous':
                $date->modify('-1 week');
                break;

            case "upcoming":
                // Cutoff is on Thursday, so go forward by 2 weeks
                if ($date->format('N') >= 4) {
                    $date->modify('+2 week');
                } else {
                    // Same as next week
                    $date->modify('+1 week');
                }
                break;

            case "afternext":
                $date->modify('+2 week');
                break;

            case 'next':
                $date->modify('+1 week');
                break;
            default:
                return null;
        }

        return $date;
    }

    /**
     * Parse an identifier, ex: 2015-W09
     *
     * @param  string $input
     * @return DateTimeInterface|null
     */
    protected function parseIdentifier($input): ?DateTimeInterface
    {
        $pieces = explode('-', $input);

        if (count($pieces) != 2) {
            return null;
        }

        list($year, $week) = $pieces;
        $dateString = strtoupper($year . $week);

        if (!strtotime($dateString)) {
            return null;
        }

        return new DateTimeImmutable($dateString);
    }

    public function __toString()
    {
        return $this->getId() ?: '';
    }
}
