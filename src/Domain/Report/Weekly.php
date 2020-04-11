<?php

namespace App\Domain\Report;

use App\Domain\Report\Weekly\Report;
use App\Infrastructure\Date\Week;
use Gedmo\Timestampable\Traits\Timestampable;

class Weekly
{
    use Timestampable;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Week
     */
    private $week;

    /**
     * @var Report
     */
    private $report;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeek(): Week
    {
        return $this->week;
    }

    public function setWeek(Week $week): self
    {
        $this->week = $week;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): self
    {
        $this->report = $report;

        return $this;
    }
}
