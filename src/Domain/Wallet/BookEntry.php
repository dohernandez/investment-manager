<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Exception\BookEntryNotEqualsException;
use App\Infrastructure\Money\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BookEntry
{
    public const TYPE_BOOK = 'book';
    public const TYPE_YEAR = 'year';
    public const TYPE_MONTH = 'month';

    /**
     * @var int
     */
    private $id;

    /**
     * @var BookEntry|null
     */
    private $parent;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Money|null
     */
    private $total;

    /**
     * @var Collection
     */
    private $entries;

    public function __construct(?int $id = null)
    {
        $this->id = $id;

        $this->entries = new ArrayCollection();
    }

    public static function createBookEntry(string $name): self
    {
        $self = new static();

        $self->type = self::TYPE_BOOK;
        $self->name = $name;

        return $self;
    }

    public static function createYearEntry(BookEntry $parent, string $name): self
    {
        $self = new static();

        $self->parent = $parent;
        $self->type = self::TYPE_YEAR;
        $self->name = $name;

        return $self;
    }

    public static function createMonthEntry(BookEntry $parent, string $name): self
    {
        $self = new static();

        $self->parent = $parent;
        $self->type = self::TYPE_MONTH;
        $self->name = $name;

        return $self;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): ?BookEntry
    {
        return $this->parent;
    }

    public function setParent(?BookEntry $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTotal(): ?Money
    {
        return $this->total;
    }

    public function setTotal(?Money $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function increaseTotal(?Money $money): self
    {
        $this->total = $this->total ? $this->total->increase($money) : $money;

        return $this;
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function setEntries(Collection $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function getBookEntry(string $name): ?BookEntry
    {
        $bookEntry = $this->entries->filter(function (BookEntry $entry) use($name) {
            if ($entry->getName() === $name) {
                return true;
            }

            return false;
        })->first();
        return $bookEntry ? $bookEntry : null;
    }

    public function merge(?BookEntry $bookEntry): self
    {
        if (!$bookEntry) {
            return $this;
        }

        if ($this->getType() !== $bookEntry->getType() || $this->getName() !== $bookEntry->getName()) {
            throw new BookEntryNotEqualsException();
        }

        $this->setTotal($bookEntry->getTotal());

        /** @var BookEntry $bEntry */
        foreach ($bookEntry->getEntries() as $bEntry) {
            $merged = false;
            /** @var BookEntry $entry */
            foreach ($this->getEntries() as $entry) {
                try {
                    $entry->merge($bEntry);
                    $merged = true;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$merged) {
                $bEntry->setParent($this);
                $this->entries->add($bEntry);
            }
        }

        return $this;
    }
}
